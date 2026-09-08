<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'auth.php';
require_once 'db.php';

// Nếu đã đăng nhập rồi thì chuyển thẳng vào trang chủ, không cho ở lại trang login
if (is_logged_in()) {
    header("Location: trangchu.php");
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error[] = "Vui lòng nhập Tên đăng nhập và Mật khẩu.";
    }

    if(login($username, $password)){
        header('location: trangchu.php');
        exit;
    } else $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>
    <style>
    body {
        font-family: Arial;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background: #f0f2f5;
    }

    .login-box {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        width: 300px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button {
        width: 100%;
        padding: 10px;
        background: #333;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background: #555;
    }

    .error {
        color: red;
        font-size: 14px;
        text-align: center;
    }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>Đăng nhập</h2>
        <?php if($error): ?>
        <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>

</html>
