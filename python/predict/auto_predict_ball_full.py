import pandas as pd
import numpy as np
import sys
import os

# ==========================
# 参数输入
# ==========================
if len(sys.argv) >= 2:
    FILENAME = sys.argv[1]
else:
    FILENAME = "pl3.csv"

ALPHA = 0.1  # 冷号补偿系数

# 搜索窗口和验证期闭区间
WINDOW_START = 20
WINDOW_END = 40
VALID_START = 5
VALID_END = 15

# ==========================
# 文件读取与检查
# ==========================
if not os.path.exists(FILENAME):
    raise FileNotFoundError(f"文件 {FILENAME} 不存在，请检查路径。")

data = pd.read_csv(FILENAME, header=None)[0].astype(str).tolist()

# ==========================
# 工具函数
# ==========================
def split_digits(num_str):
    return [int(num_str[0]), int(num_str[1]), int(num_str[2])]

def weighted_freq(data):
    weights = [0.95 ** i for i in range(len(data))][::-1]
    pos_freq = {p: {i: 0 for i in range(10)} for p in range(3)}
    for idx, row in enumerate(data):
        for p in range(3):
            pos_freq[p][row[p]] += weights[idx]
    return pos_freq

def cold_balance_freq(data):
    base = weighted_freq(data)
    pos_last_seen = {p: {i: None for i in range(10)} for p in range(3)}
    for idx, row in enumerate(data[::-1]):
        for p in range(3):
            if pos_last_seen[p][row[p]] is None:
                pos_last_seen[p][row[p]] = idx
    max_miss = {p: max(v for v in pos_last_seen[p].values() if v is not None) for p in range(3)}
    for p in range(3):
        for i in range(10):
            miss = pos_last_seen[p][i] if pos_last_seen[p][i] is not None else max_miss[p]
            base[p][i] += ALPHA * (miss / max_miss[p])
    return base

def multi_feature_score(data):
    base = weighted_freq(data)
    sums = [sum(x) for x in data]
    avg_sum = np.mean(sums)
    odd_balance = sum(sum(d % 2 for d in x) for x in data) / (len(data)*3)
    for p in range(3):
        for i in range(10):
            sum_effect = 1 - abs(i - avg_sum/3) / 9
            odd_effect = 1 - abs((i % 2) - odd_balance)
            base[p][i] = 0.5*base[p][i] + 0.3*sum_effect + 0.2*odd_effect
    return base

def select_top2(freq):
    return {p: [k for k,_ in sorted(v.items(), key=lambda x:x[1], reverse=True)[:2]] for p,v in freq.items()}

def check_hit_in_future(pred, future_data):
    for actual in future_data:
        if all(actual[p] in pred[p] for p in range(3)):
            return True, actual
    return False, None

# ==========================
# 数据预处理
# ==========================
data = data[::-1]  # 时间顺序
data = [split_digits(x.zfill(3)) for x in data]

# ==========================
# 自动搜索最佳窗口 + 验证期数
# ==========================
window_list = range(WINDOW_START, WINDOW_END + 1)
valid_len_list = range(VALID_START, VALID_END + 1)

best_overall_rate = 0
best_overall_params = None
best_overall_results = None
best_overall_records = None

for WINDOW in window_list:
    for VALID_LEN in valid_len_list:
        min_required = WINDOW + VALID_LEN
        if len(data) < min_required:
            continue

        step = VALID_LEN
        total = len(data) - WINDOW - VALID_LEN + 1
        results = {"热度加权":0, "冷热平衡":0, "多特征融合":0}
        records = []

        for i in range(0, total, step):
            train = data[i:i+WINDOW]
            test_future = data[i+WINDOW:i+WINDOW+VALID_LEN]
            if len(test_future) < VALID_LEN:
                continue
            for name, func in {
                "热度加权": weighted_freq,
                "冷热平衡": cold_balance_freq,
                "多特征融合": multi_feature_score
            }.items():
                freq = func(train)
                pred = select_top2(freq)
                hit, actual_hit = check_hit_in_future(pred, test_future)
                if hit:
                    results[name] += 1
                    records.append({
                        "方法": name,
                        "百位预测1": pred[0][0],
                        "百位预测2": pred[0][1],
                        "十位预测1": pred[1][0],
                        "十位预测2": pred[1][1],
                        "个位预测1": pred[2][0],
                        "个位预测2": pred[2][1],
                        "实际百位": actual_hit[0],
                        "实际十位": actual_hit[1],
                        "实际个位": actual_hit[2]
                    })

        if total > 0:
            rates = {k: v/total*100 for k,v in results.items()}
            max_rate = max(rates.values())
            if max_rate > best_overall_rate:
                best_overall_rate = max_rate
                best_overall_params = (WINDOW, VALID_LEN)
                best_overall_results = rates
                best_overall_records = records

# ==========================
# 使用最佳参数预测最新一期
# ==========================
WINDOW, VALID_LEN = best_overall_params
step = VALID_LEN
latest_train = data[-WINDOW - VALID_LEN + step:]
best_alg = max(best_overall_results, key=best_overall_results.get)
best_func = {
    "热度加权": weighted_freq,
    "冷热平衡": cold_balance_freq,
    "多特征融合": multi_feature_score
}[best_alg]
latest_pred = select_top2(best_func(latest_train[-WINDOW:]))

# 保存最新预测
best_overall_records.append({
    "方法": "最新预测",
    "百位预测1": latest_pred[0][0],
    "百位预测2": latest_pred[0][1],
    "十位预测1": latest_pred[1][0],
    "十位预测2": latest_pred[1][1],
    "个位预测1": latest_pred[2][0],
    "个位预测2": latest_pred[2][1],
    "实际百位": "-",
    "实际十位": "-",
    "实际个位": "-"
})

# ==========================
# 保存结果到 CSV (UTF-8-SIG)
# ==========================
df_records = pd.DataFrame(best_overall_records)
df_records.to_csv("result.csv", index=False, encoding="utf-8-sig")

with open("result.csv", "a", encoding="utf-8-sig") as f:
    f.write("\n算法历史胜率：\n")
    for k,v in best_overall_results.items():
        f.write(f"{k},{v:.1f}%\n")
    f.write(f"最佳算法,{best_alg}（胜率 {best_overall_results[best_alg]:.1f}%）\n")
    f.write(f"最佳窗口大小,{WINDOW},最佳预测期数,{VALID_LEN}\n")

# ==========================
# 屏幕输出
# ==========================
print("🎯 三种算法预测胜率（未来命中算成功）：")
for k,v in best_overall_results.items():
    print(f" - {k}：{v:.1f}%")

print(f"\n🏆 最佳算法：{best_alg}（胜率 {best_overall_results[best_alg]:.1f}%）")
print(f"🔧 最佳窗口大小: {WINDOW}, 最佳预测期数: {VALID_LEN}")

print("\n🔮 预测最新一期复式号码：")
for pos_name, idx in zip(["百位", "十位", "个位"], range(3)):
    print(f"{pos_name}：{latest_pred[idx]}")

print(f"\n✅ 所有预测详情及胜率已保存到 result.csv")
