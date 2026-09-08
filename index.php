<?php
// index.php
// File này chỉ đóng vai trò "cổng vào" (entry point) để Azure/web server
// tìm thấy khi người dùng truy cập vào địa chỉ gốc của website.
// Nó KHÔNG chứa logic hay giao diện riêng - chỉ chuyển hướng đến
// đúng trang mà hệ thống hiện tại đang dùng (trangchu.php / login.php),
// nên toàn bộ chức năng và giao diện giữ nguyên như cũ.
 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    // Đã đăng nhập -> vào thẳng trang chủ
    header("Location: trangchu.php");
} else {
    // Chưa đăng nhập -> ra trang đăng nhập
    header("Location: login.php");
}
exit;
 
