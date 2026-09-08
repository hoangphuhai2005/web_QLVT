<?php
// FILE: qlyKH-db.php

// --- CẤU HÌNH DATABASE RIÊNG CHO MODULE KHÁCH HÀNG ---
/*
Database: ql_kh_va_order
Tables: customers, orders
*/

// Đổi tên biến toàn cục để không đụng độ với biến $conn của hệ thống chính
global $conn_kh;

// 1. ĐỔI TÊN HÀM KẾT NỐI: connect_db -> connect_db_kh
function connect_db_kh(){
    global $conn_kh;
    if($conn_kh === null){
        // Kết nối đến DB riêng: ql_kh_va_order
        $conn_kh = new mysqli('localhost', 'root', '', 'web_qlvt');
        
        if($conn_kh->connect_errno){
            die("Lỗi kết nối DB Khách hàng: (" . $conn_kh->connect_errno . ") " . $conn_kh->connect_error);
        } else {
            mysqli_set_charset($conn_kh, 'utf8');
        }
    }
    return $conn_kh;
}

// 2. ĐỔI TÊN HÀM NGẮT KẾT NỐI
// CODE ĐÚNG (Sửa thành thế này)
function disconnect_db_kh() {  // <--- Đổi tên hàm ở đây
    global $conn_kh;         // <--- Đổi tên biến (nếu chưa đổi)
    if ($conn_kh) {
        mysqli_close($conn_kh);
        $conn_kh = null;
    }
}

// Hàm Helper: Bảo vệ output HTML
// Kiểm tra nếu hàm e() chưa tồn tại thì mới định nghĩa (tránh xung đột với các file khác)
if (!function_exists('e')) {
    function e($s){
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Hàm Helper: Lấy class CSS dựa trên Status
function get_status_class($status) {
    switch ($status) {
        case 'Pending': return 'status-Pending';
        case 'InTransit': return 'status-InTransit';
        case 'Delivered': return 'status-Delivered';
        case 'Cancelled': return 'status-Cancelled';
        default: return 'status-default';
    }
}

// --- CÁC HÀM XỬ LÝ DỮ LIỆU (Đã cập nhật gọi connect_db_kh) ---

function add_customer($name, $email, $phone, $address) {
    $conn = connect_db_kh(); // Gọi hàm kết nối mới
    $stmt = mysqli_prepare($conn, "INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $phone, $address);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function get_all_customers() {
    $conn = connect_db_kh(); // Gọi hàm kết nối mới
    $customers = [];
    $res = mysqli_query($conn, "SELECT * FROM customers ORDER BY created_at DESC");
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $customers[] = $r;
        }
        mysqli_free_result($res);
    }
    return $customers;
}

function add_order($customer_id, $route_from, $route_to, $product_name, $weight, $cargo_type, $status, $price) {
    $conn = connect_db_kh(); // Gọi hàm kết nối mới
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (customer_id, route_from, route_to, product_name, weight, cargo_type, status, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) return false;

    $weight_s = (string) floatval($weight);
    $price_s = (string) floatval($price);
    
    mysqli_stmt_bind_param($stmt, 'isssssss', $customer_id, $route_from, $route_to, $product_name, $weight_s, $cargo_type, $status, $price_s); 
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function update_order_status($order_id, $new_status) {
    $conn = connect_db_kh(); // Gọi hàm kết nối mới
    $allowed_statuses = ['Pending', 'InTransit', 'Delivered', 'Cancelled'];
    
    if ($order_id <= 0 || !in_array($new_status, $allowed_statuses)) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    if (!$stmt) return false;
    
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

function get_orders_with_customer_name() {
    $conn = connect_db_kh(); // Gọi hàm kết nối mới
    $orders = [];
    // Join bảng orders và customers
    $sql = "SELECT o.*, c.name AS customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC";
    $res = mysqli_query($conn, $sql);
    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $orders[] = $r;
        }
        mysqli_free_result($res);
    }
    return $orders;
}

// Khởi tạo kết nối ngay khi load file
connect_db_kh();
?>