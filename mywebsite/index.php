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
        header("Location: stories.php");
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
            position: relative;
        }
        .container h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
            position: relative;
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
        .error-message {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            font-size: 0.875em;
            box-sizing: border-box;
            z-index: 1;
        }
		footer {
            background-color: rgba(241, 241, 241, 0); 
            padding: 10px;
            text-align: center;
        }
		footer p {
			font-size: 10px;
			margin-top: 15%;
		}
    </style>
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
        <p><a href="register.php">没有账户？点击这里注册</a></p>
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
