<?php include 'fetch_diaries.php' ?>

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
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            margin-left: calc(20% + 30px); /* 留出侧边栏和边距的空间 */
            margin-right: 20px; /* 留出右侧间距 */
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            min-height: calc(100vh - 40px); /* 留出上下的空间 */
            margin-top: 80px; /* 留出顶部空间 */
            margin-bottom: 20px; /* 留出底部空间 */
            position: relative;
        }

        .sidebar {
            width: 20%;
            max-width: 260px; /* 限制最大宽度 */
            background: linear-gradient(to bottom right, #ffb3c6, #ffe0e6); /* 粉色渐变背景 */
            border: 2px solid #ff69b4; /* 粉色边框 */
            border-radius: 15px; /* 圆角边框 */
            padding: 10px;
            overflow-y: auto; /* 内容超出时显示垂直滚动条 */
            position: fixed; /* 固定定位 */
            top: 80px;
            left: 60px;
            bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* 添加阴影 */
        }

        /* Updated CSS for date hover and click effects */
        .accordion-date-header {
            cursor: pointer;
            font-size: 16px;
            padding: 10px;
        }

        .accordion-date-header:hover {
            color: #007bff;
            font-weight: bold;
        }

        .accordion-date-header.active {
            color: #0056b3;
            font-weight: bold;
        }

        .accordion {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .accordion-header {
            background-color: #f1f1f1;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            border: none;
            border-radius: 0;
            text-align: left;
            display: flex;
            align-items: center;
        }

        .accordion-header::before {
            content: '+';
            display: inline-block;
            margin-right: 10px;
            font-size: 18px;
        }

        .accordion-header.active::before {
            content: '-';
        }

        .accordion-content {
            display: none;
            padding: 10px;
            background-color: white;
        }

        .accordion-content p {
            margin: 0;
        }

        .logout-btn {
            position: fixed; 
            top: 20px; 
            right: 20px; 
            background-color: #ff69b4; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 5px; 
            text-decoration: none; 
            font-size: 14px; 
            cursor: pointer; 
            transition: background-color 0.3s; 
        }
        
        .logout-btn:hover {
            background-color: #ff1493; 
        }
        
        .homepage-btn {
            position: fixed;
            top: 20px;
            left: 60px;
            background-color: transparent;
            color: #ff69b4;
            border: 2px solid #ff69b4;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .homepage-btn:hover {
            background-color: #ff69b4;
            color: white;
        }

        #diary-display {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 20px;
            min-height: 100px;
            background-color: #f9f9f9;
            max-width: 600px; /* 设定最大宽度 */
            position: fixed; /* 固定定位 */
            right: 20px; /* 右侧间距 */
            top: 80px; /* 距离顶部 */
            bottom: 20px; /* 距离底部 */
            overflow-y: auto; /* 内容超出时显示垂直滚动条 */
            box-sizing: border-box; /* 包括边框和内边距在宽度计算中 */
        }

        .default-content {
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <a href="homepage.php" class="homepage-btn">I♥Y</a>
    <a href="logout.php" class="logout-btn">注销</a>
    <div class="container">
        <div class="sidebar">
            <?php foreach ($diaries as $username => $years): ?>
                <div class="accordion">
                    <div class="accordion-header"><?php echo htmlspecialchars($username); ?></div>
                    <div class="accordion-content">
                        <?php foreach ($years as $year => $months): ?>
                            <div class="accordion">
                                <div class="accordion-header"><?php echo htmlspecialchars($year); ?></div>
                                <div class="accordion-content">
                                    <?php foreach ($months as $month => $days): ?>
                                        <div class="accordion">
                                            <div class="accordion-header"><?php echo htmlspecialchars($month); ?>月</div>
                                            <div class="accordion-content">
                                                <?php foreach ($days as $day => $contents): ?>
                                                    <div class="accordion-date-header" data-contents='<?php echo json_encode($contents); ?>'>
                                                        <?php echo htmlspecialchars($day); ?>日
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!--div id="diary-display" class="default-content">
        欢迎！点击日期查看日记内容。
    </div-->

    <script>
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
            });
        });

        document.querySelectorAll('.accordion-date-header').forEach(dateHeader => {
            dateHeader.addEventListener('click', () => {
                // 移除其他日期的高亮状态
                document.querySelectorAll('.accordion-date-header').forEach(header => {
                    header.classList.remove('active');
                });
                // 高亮当前日期
                dateHeader.classList.add('active');

                // 更新日记展示区域内容
                const diaryDisplay = document.getElementById('diary-display');
                const contents = JSON.parse(dateHeader.dataset.contents);
                diaryDisplay.innerHTML = contents.length > 0 ? contents.join('<br><br>') : '没有日记内容。';
                diaryDisplay.classList.remove('default-content');
            });
        });
    </script>
</body>
</html>
