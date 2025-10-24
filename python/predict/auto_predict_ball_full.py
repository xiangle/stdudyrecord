import argparse
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, r2_score
from statistics import mean

# ===============================
# 🧩 特征工程
# ===============================
def add_features(df):
    df = df.copy()
    df["sum"] = df["m1"] + df["m2"] + df["m3"]
    df["diff_max_min"] = df[["m1","m2","m3"]].max(axis=1) - df[["m1","m2","m3"]].min(axis=1)
    df["odd_count"] = df[["m1","m2","m3"]].apply(lambda x: sum(v%2 for v in x), axis=1)
    return df

# ===============================
# 🧠 滑动窗口构建
# ===============================
def make_samples(df, window):
    X, y1, y2, y3 = [], [], [], []
    for i in range(len(df)-window):
        window_data = df.iloc[i:i+window]
        X.append(window_data.values.flatten())
        target = df.iloc[i+window]
        y1.append(target["m1"])
        y2.append(target["m2"])
        y3.append(target["m3"])
    return np.array(X), np.array(y1), np.array(y2), np.array(y3)

# ===============================
# 🎯 模型训练 + 验证
# ===============================
def train_and_eval(df, window):
    X, y1, y2, y3 = make_samples(df, window)
    if len(X) < 10:
        return None
    X_train, X_val, y1_train, y1_val = train_test_split(X, y1, test_size=0.2, random_state=42)
    _, _, y2_train, y2_val = train_test_split(X, y2, test_size=0.2, random_state=42)
    _, _, y3_train, y3_val = train_test_split(X, y3, test_size=0.2, random_state=42)

    models = [RandomForestRegressor(n_estimators=300, random_state=42) for _ in range(3)]
    for m, y_train in zip(models, [y1_train, y2_train, y3_train]):
        m.fit(X_train, y_train)

    # 计算验证集准确性
    val_preds = [m.predict(X_val) for m in models]
    for i, (y_val, pred) in enumerate(zip([y1_val, y2_val, y3_val], val_preds), 1):
        mae = mean_absolute_error(y_val, pred)
        r2 = r2_score(y_val, pred)
        print(f"机器{i}模型验证准确性: MAE={mae:.3f}, R²={r2:.3f}")

    return {"window": window, "models": models}

# ===============================
# 🔢 预测下一天 + 概率分布
# ===============================
def predict_prob(models, df, window):
    last_window = df.tail(window).values.flatten().reshape(1, -1)
    results = []

    for model in models:
        # 每棵树预测整数
        tree_preds = [round(tree.predict(last_window)[0]) for tree in model.estimators_]
        # 限制 0-9
        tree_preds = [min(max(0, p), 9) for p in tree_preds]

        # 统计概率
        counts = [tree_preds.count(i) for i in range(10)]
        total = sum(counts)
        probs = [c/total for c in counts]

        # 最可能的预测
        pred = np.argmax(probs)
        results.append({"pred": pred, "probs": probs})
    return results

# ===============================
# 🔍 自动优化窗口
# ===============================
def auto_optimize(df, windows):
    best_result = None
    for w in windows:
        print(f"\n尝试窗口大小: {w}")
        r = train_and_eval(df, w)
        if r:
            best_result = r  # 这里简单选最后一个有效窗口，可按R²优化
    print(f"\n选择窗口大小: {best_result['window']}")
    return best_result

# ===============================
# 📂 主函数
# ===============================
def main(args):
    with open(args.csv,'r',encoding='utf-8') as f:
        lines = [line.strip() for line in f.readlines() if line.strip()]
    data = [[int(line[0]), int(line[1]), int(line[2])] for line in lines]
    df = pd.DataFrame(data, columns=["m1","m2","m3"])
    df = add_features(df)
    df = df.tail(args.train_days).reset_index(drop=True)

    best = auto_optimize(df, args.windows)
    preds = predict_prob(best["models"], df, best["window"])

    # 输出概率分布
    for i, p in enumerate(preds, 1):
        print(f"\n机器{i}预测最可能结果: {p['pred']}")
        print("概率分布:")
        for num, prob in enumerate(p['probs']):
            print(f"  {num}: {prob*100:.1f}%")

# python auto_predict_ball_full.py --csv balls.csv --train_days 120 --windows 10 20 30 40 50
if __name__=="__main__":
    parser = argparse.ArgumentParser(description="整数+概率分布+验证准确性+自动窗口优化")
    parser.add_argument("--csv", type=str, default="balls.csv", help="输入文件，每行如 234")
    parser.add_argument("--train_days", type=int, default=120, help="用于训练的最近天数")
    parser.add_argument("--windows", nargs="+", type=int, default=[10,20,30,40,50], help="尝试窗口大小")
    args = parser.parse_args()
    main(args)
