<?php
$password = '123456';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Mật khẩu: " . $password . "<br>";
echo "Chuỗi hash mới của bạn là: <br><strong>" . $hash . "</strong>";
?>