import tkinter as tk
import threading
import requests
import time
import ctypes
import sys
import pystray
from PIL import Image, ImageDraw
import json
import os

# 获取屏幕尺寸
user32 = ctypes.windll.user32
SCREEN_WIDTH = user32.GetSystemMetrics(0)
SCREEN_HEIGHT = user32.GetSystemMetrics(1)

CONFIG_FILE = "config.json"

class StockTicker:
    def __init__(self):
        self.load_config()

        # 初始化窗口
        self.root = tk.Tk()
        self.root.overrideredirect(True)
        self.root.attributes("-topmost", True)
        self.root.attributes("-alpha", self.alpha)
        self.root.configure(bg="white")

        self.scale = 1.0  # 缩放因子
        self.base_width = self.width
        self.base_height = self.height

        self.root.geometry(f"{self.base_width}x{self.base_height}+100+100")

        # 标签容器
        self.frame = tk.Frame(self.root, bg="white")
        self.frame.pack(fill="both", expand=True)
        self.labels = []
        self.create_labels()

        # 事件绑定
        self.root.bind("<ButtonPress-1>", self.start_move)
        self.root.bind("<B1-Motion>", self.do_move)
        self.root.bind("<ButtonRelease-1>", self.snap_to_edge)
        self.root.bind("<MouseWheel>", self.on_mouse_wheel)
        self.root.bind("<Double-Button-1>", lambda e: self.close())
        self.root.bind("<Button-3>", self.minimize_to_tray)

        self.drag_data = {"x": 0, "y": 0}
        self.running = True
        threading.Thread(target=self.update_loop, daemon=True).start()

        self.tray_icon = None
        self.refresh_interval = 1  # 刷新频率秒数

    def load_config(self):
        """加载配置"""
        if not os.path.exists(CONFIG_FILE):
            raise FileNotFoundError(f"找不到配置文件 {CONFIG_FILE}")
        with open(CONFIG_FILE, "r", encoding="utf-8") as f:
            config = json.load(f)
        self.width = config.get("width", 800)
        self.height = config.get("height", 50)
        self.alpha = config.get("alpha", 0.7)
        self.stocks = config.get("stocks", [])

    def create_labels(self):
        """创建标签"""
        for lbl in self.labels:
            lbl.destroy()
        self.labels.clear()
        for _ in self.stocks:
            lbl = tk.Label(self.frame, text="加载中...", fg="white", bg="white", anchor="w")
            lbl.place(y=0, height=self.base_height)
            self.labels.append(lbl)
        self.update_layout()

    def update_layout(self):
        """更新布局和字体"""
        gap = 10
        n = len(self.labels)
        total_gap = gap * (n + 1)
        width = int(self.base_width * self.scale)
        height = int(self.base_height * self.scale)
        available_width = width - total_gap
        label_width = available_width // n if n else width

        font_size = max(8, int(min(label_width / 6, height * 0.8)))

        self.root.geometry(f"{width}x{height}+{self.root.winfo_x()}+{self.root.winfo_y()}")

        for i, lbl in enumerate(self.labels):
            x = gap + i * (label_width + gap)
            lbl.place(x=x, y=0, width=label_width, height=height)
            lbl.config(font=("微软雅黑", font_size))

    def fetch_stock_price(self, code):
        """获取股票数据"""
        url = f"https://qt.gtimg.cn/q={code}"
        try:
            res = requests.get(url, timeout=5)
            res.encoding = 'gbk'
            data = res.text.split('~')
            if len(data) < 33:
                return "数据异常", "white"
            name = data[1]
            price = data[3]
            percent = data[32]
            try:
                p = float(percent)
            except:
                p = 0.0
            color = 'red' if p > 0 else 'green' if p < 0 else 'grey'
            return f"{name}: {price}元 ({percent}%)", color
            # return f"{name}", color
        except:
            return "获取失败", "white"

    def update_loop(self):
        """更新股票数据"""
        while self.running:
            for i, code in enumerate(self.stocks):
                text, color = self.fetch_stock_price(code)
                def update_label(i=i, text=text, color=color):
                    if i < len(self.labels):
                        self.labels[i].config(text=text, fg=color)
                    self.adjust_window_width()
                self.root.after(0, update_label)
            time.sleep(self.refresh_interval)

    def adjust_window_width(self):
        """自适应窗口宽度以完整显示内容"""
        test_font = self.labels[0].cget("font")
        test_label = tk.Label(self.root, font=test_font)
        max_text_width = 0
        for lbl in self.labels:
            text = lbl.cget("text")
            test_label.config(text=text)
            test_label.update_idletasks()
            width = test_label.winfo_reqwidth()
            max_text_width = max(max_text_width, width)
        test_label.destroy()

        min_width = int(self.base_width * self.scale)
        desired_width = max(min_width, max_text_width * len(self.labels) + 20 * (len(self.labels) + 1))

        current_geometry = self.root.geometry().split('+')
        position = (int(current_geometry[1]), int(current_geometry[2]))
        self.root.geometry(f"{desired_width}x{int(self.base_height * self.scale)}+{position[0]}+{position[1]}")

        gap = 10
        available_width = desired_width - gap * (len(self.labels) + 1)
        label_width = available_width // len(self.labels)
        for i, lbl in enumerate(self.labels):
            x = gap + i * (label_width + gap)
            lbl.place(x=x, y=0, width=label_width, height=int(self.base_height * self.scale))

    def on_mouse_wheel(self, event):
        """鼠标滚轮缩放"""
        delta = event.delta // 120
        new_scale = self.scale + delta * 0.1
        if 0.5 <= new_scale <= 2.5:
            self.scale = new_scale
            self.update_layout()
            self.adjust_window_width()

    def start_move(self, event):
        """开始拖动"""
        self.drag_data["x"] = event.x
        self.drag_data["y"] = event.y

    def do_move(self, event):
        """拖动窗口"""
        x = event.x_root - self.drag_data["x"]
        y = event.y_root - self.drag_data["y"]
        x = max(0, min(x, SCREEN_WIDTH - self.base_width))
        y = max(0, min(y, SCREEN_HEIGHT - self.base_height))
        self.root.geometry(f"{self.root.winfo_width()}x{self.root.winfo_height()}+{x}+{y}")

    def snap_to_edge(self, event):
        """吸附屏幕边缘"""
        x = self.root.winfo_x()
        y = self.root.winfo_y()
        margin = 20
        snap_x = x
        snap_y = y

        if x < margin:
            snap_x = 0
        elif x + self.root.winfo_width() > SCREEN_WIDTH - margin:
            snap_x = SCREEN_WIDTH - self.root.winfo_width()

        if y < margin:
            snap_y = 0
        elif y + self.root.winfo_height() > SCREEN_HEIGHT - margin:
            snap_y = SCREEN_HEIGHT - self.root.winfo_height()

        self.root.geometry(f"{self.root.winfo_width()}x{self.root.winfo_height()}+{snap_x}+{snap_y}")

    def minimize_to_tray(self, event=None):
        """最小化到托盘"""
        self.root.withdraw()
        if self.tray_icon is None:
            icon_image = self.create_icon()
            self.tray_icon = pystray.Icon("StockTicker", icon_image, "股票行情条", menu=pystray.Menu(
                pystray.MenuItem("显示", self.show_window),
                pystray.MenuItem("退出", self.exit_app)
            ))
            threading.Thread(target=self.tray_icon.run, daemon=True).start()

    def create_icon(self):
        """生成托盘图标"""
        image = Image.new('RGB', (64, 64), (0, 0, 0))
        draw = ImageDraw.Draw(image)
        draw.rectangle((16, 16, 48, 48), fill="red")
        return image

    def show_window(self, icon=None, item=None):
        """恢复窗口"""
        self.root.after(0, self.root.deiconify)
        if self.tray_icon:
            self.tray_icon.stop()
            self.tray_icon = None

    def exit_app(self, icon=None, item=None):
        """退出程序"""
        self.running = False
        if self.tray_icon:
            self.tray_icon.stop()
        self.root.after(0, self.root.destroy)
        sys.exit()

    def close(self):
        """关闭窗口"""
        self.running = False
        self.root.destroy()


if __name__ == "__main__":
    app = StockTicker()
    app.root.mainloop()
