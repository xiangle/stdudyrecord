<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "myweb");

if ($mysqli->connect_error) {
    die("连接失败: " . $mysqli->connect_error);
}

$stories = [];
$result = $mysqli->query("SELECT * FROM stories");
while ($row = $result->fetch_assoc()) {
    $stories[] = $row;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>日记</title>
    <style>
        body {
            background: url('story.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            position: relative; /* 确保相对定位用于子元素的绝对定位 */
        }
        .container {
            width: 80%;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
			min-height: 500px;
        }
        .tab {
            overflow: hidden;
            background-color: #f1f1f1;
        }
        .tab button {
            background-color: #ddd;
            border: none;
            outline: none;
            padding: 14px 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        .tab button:hover {
            background-color: #ccc;
        }
        .tabcontent {
            display: none;
            padding: 20px;
            background-color: white;
        }
        .tab button.active {
            background-color: #ccc;
        }
		.logout-btn {
			position: fixed; /* 使用固定定位以保持在页面右上角 */
			top: 20px; /* 距离浏览器顶部 20 像素 */
			right: 20px; /* 距离浏览器右侧 20 像素 */
			background-color: #ff69b4; /* 浅粉色背景 */
			color: white; /* 设置文字颜色 */
			border: none; /* 去掉默认边框 */
			padding: 10px 20px; /* 设置内边距 */
			border-radius: 5px; /* 设置圆角 */
			text-decoration: none; /* 去掉下划线 */
			font-size: 14px; /* 设置字体大小 */
			cursor: pointer; /* 设置鼠标悬停时为手形 */
			transition: background-color 0.3s; /* 添加背景颜色过渡效果 */
		}

		.logout-btn:hover {
			background-color: #ff1493; /* 鼠标悬停时更改背景颜色为深粉色 */
	}
    </style>
</head>
<body>
    <a href="logout.php" class="logout-btn">注销</a>
    <div class="container">
        <h1>欢迎, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <div class="tab">
            <?php foreach ($stories as $index => $story): ?>
                <button class="tablinks" onclick="openTab(event, 'story<?php echo $story['id']; ?>')"><?php echo htmlspecialchars($story['title']); ?></button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($stories as $story): ?>
            <div id="story<?php echo $story['id']; ?>" class="tabcontent">
                <h3><?php echo htmlspecialchars($story['title']); ?></h3>
                <p><?php echo htmlspecialchars($story['content']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function openTab(evt, storyId) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(storyId).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>
</html>
