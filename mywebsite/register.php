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
    <style>
        body {
            background: url('login.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 300px;
            margin: 100px auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-group input[type="submit"] {
            width: 100%;
            background-color: transparent;
            color: transparent;
            border: none;
            cursor: pointer;
            height: 40px;
            background-image: url('tab.png');
            background-size: 100% 40px;
            background-repeat: no-repeat;
            background-position: center center;
            text-align: center;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .container a {
            display: block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .container a:hover {
            text-decoration: underline;
        }
        .gif-container {
            position: fixed;
            top: 10px;
            left: 10px;
            width: 100px;
            height: auto;
            z-index: 1000;
        }
        .gif-container img {
            width: 100%;
            height: auto;
        }
    </style>
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
