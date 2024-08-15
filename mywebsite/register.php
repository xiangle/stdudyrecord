<?php
$mysqli = new mysqli("localhost", "root", "", "myweb");

if ($mysqli->connect_error) {
    die("连接失败: " . $mysqli->connect_error);
}

$message = ''; // Initialize message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        $message = "<p style='color: green;'>注册成功！</p><p><a href='index.php'>返回登录页面</a></p>";
    } else {
        $message = "<p style='color: red;'>错误: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>用户注册</title>
    <link rel="stylesheet" href="register.css"> <!-- 链接到 CSS 文件 -->
</head>
<body>
    <div class="gif-container">
        <img src="hellokitty.gif" alt="GIF Image">
    </div>
    <div class="container">
        <h2>用户注册</h2>
        <?php echo $message; ?> <!-- Display the message here -->
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="注册">
            </div>
        </form>
        <p><a href="index.php">已有账户？点击这里登录</a></p>
    </div>
</body>
</html>
