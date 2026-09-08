<?php
if(session_status() === PHP_SESSION_NONE) session_start();

// Gọi file kết nối (Chứa biến $conn chuẩn PDO)
require_once 'db.php';

// Hàm kiểm tra đã đăng nhập chưa
function is_logged_in() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

// Chặn truy cập nếu chưa đăng nhập
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// --- HÀM LOGIN (ĐÃ SỬA SANG PDO) ---
function login($username, $password){
    global $conn; // Lấy biến kết nối $conn từ db.php

    // 1. Chuẩn bị câu lệnh SQL
    // (Dựa trên tên bảng 'nhanvien' bạn cung cấp trước đó)
    $sql = "SELECT username, password_hash, ten_nhan_vien, nv_id FROM nhanvien WHERE username = ?";
    
    try {
        $stmt = $conn->prepare($sql);
        
        // 2. THAY ĐỔI QUAN TRỌNG Ở ĐÂY:
        // PDO không dùng bind_param. Ta truyền mảng tham số trực tiếp vào execute()
        $stmt->execute([$username]);
        
        // 3. Lấy dữ liệu (Thay thế cho get_result)
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4. Kiểm tra mật khẩu
        if($row){ 
            // ƯU TIÊN 1: Nếu mật khẩu trong DB đã được mã hóa bằng password_hash()
            if(password_verify($password, $row['password_hash'])){
                // Lưu session
                $_SESSION['user'] = $row['username']; 
                $_SESSION['user_id'] = $row['nv_id'];
                $_SESSION['full_name'] = $row['ten_nhan_vien'];
                return true;
            }
            
            // ƯU TIÊN 2: (CHỈ DÙNG ĐỂ TEST) Nếu mật khẩu trong DB là chữ thường '123456'
            // Nếu bạn đăng nhập mãi không được, hãy mở comment phần này ra
            /*
            elseif ($password == $row['password_hash']) {
                 $_SESSION['user'] = $row['username']; 
                 $_SESSION['user_id'] = $row['nv_id'];
                 $_SESSION['full_name'] = $row['ten_nhan_vien'];
                 return true;
            }
            */
        }
    } catch (PDOException $e) {
        // Ghi log lỗi nếu cần thiết
        // echo "Lỗi SQL: " . $e->getMessage();
    }
    
    return false;
}

// Hàm đăng xuất
function logout() {
    session_destroy();
    unset($_SESSION['user']);
    unset($_SESSION['user_id']);
    unset($_SESSION['full_name']);
    header("Location: login.php");
    exit;
}
?>