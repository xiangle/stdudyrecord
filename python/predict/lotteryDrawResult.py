import requests

pagenum = 2

headers = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                  "AppleWebKit/537.36 (KHTML, like Gecko) "
                  "Chrome/123.0.0.0 Safari/537.36",
    "Accept": "application/json, text/javascript, */*; q=0.01",
    "Referer": "https://example.com/",
}

for i in range(pagenum):
    url = "https://webapi.sporttery.cn/gateway/lottery/getHistoryPageListV1.qry?gameNo=85&provinceId=0&pageSize=30&isVerify=1&pageNo=" + str(i+1)
    response = requests.get(url, headers=headers)
    
    #解析 JSON 响应
    data = response.json()
    
    items = data["value"]["list"]
    
    # 提取每个元素的 lotteryDrawResult 字段
    lotteryDrawResults = [item["lotteryDrawResult"] for item in items]
    
    # 写入文件
    with open("lotteryDrawResult.txt", "a", encoding="utf-8") as f:
        for lotteryDrawResult in lotteryDrawResults:
            f.write(lotteryDrawResult + "\n")

print("✅ 已将所有名称写入 lotteryDrawResult.txt")
