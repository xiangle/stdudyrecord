<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "myweb");

if ($mysqli->connect_error) {
    die("连接失败: " . $mysqli->connect_error);
}

$error_message = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['username'] = $username;
        header("Location: homepage.php");
        exit();
    } else {
        $error_message = '用户名或密码错误！'; // Set error message
    }

    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="gif-container">
        <img src="hellokitty.gif" alt="GIF Image">
    </div>
    <div class="container">
        <h2>用户登录</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">密码:</label>
                <input type="password" id="password" name="password" required>
                <div id="error-message" class="error-message">
                    <?php echo isset($error_message) ? $error_message : ''; ?>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" value="登录">
            </div>
        </form>
        <!--p><a href="register.php">没有账户？点击这里注册</a></p-->
    </div>
    <script>
        function hideErrorMessage() {
            const errorMessage = document.getElementById('error-message');
            errorMessage.style.display = 'none';
        }

        document.getElementById('username').addEventListener('focus', hideErrorMessage);
        document.getElementById('password').addEventListener('focus', hideErrorMessage);

        // Ensure error message displays if present on page load
        window.addEventListener('load', () => {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage.textContent.trim()) {
                errorMessage.style.display = 'block';
            }
        });
    </script>
	<footer>
		<p><em>xiangle © 2024</em></p>
	</footer>
</body>
</html>
