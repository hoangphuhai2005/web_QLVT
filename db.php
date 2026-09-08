<?php

// CREATE TABLE nhanvien (
//   nv_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
//   ten_nhan_vien VARCHAR(100) NOT NULL,
//   dia_chi VARCHAR(255) DEFAULT NULL,
//   sdt VARCHAR(15) DEFAULT NULL,
//   email VARCHAR(100) NOT NULL UNIQUE,
//   username VARCHAR(50) NOT NULL UNIQUE,
//   -- Cột quan trọng để lưu mật khẩu băm, đảm bảo VARCHAR(255)
//   password_hash VARCHAR(255) NOT NULL, 
// );

// INSERT INTO nhanvien (ten_nhan_vien, dia_chi, sdt, email, username, password_hash, ngay_dang_ky) VALUES ('Lam Thần An', 'Hải Phòng', '0901234567', 'lamthanan03@gmail.com', 'nhipham', '$2y$10$izA.VIxGAtCXu84ofdGefuMKy8uJBo1h9hMDZrQi2L1UPNDj/h5fG', '2025-10-01')

global $conn;

function connect_db(){
    global $conn;
    if($conn === null){
        
                $conn = new mysqli('localhost', 'root', '', 'WEB_QLVT');
        if($conn){
            mysqli_set_charset($conn, 'utf8');
        } else echo "Kết nối thất bại";

        if($conn->connect_errno){
            die ("Lỗi kết nối DB: (" . $conn->connect_errno . ") " . $conn->connect_error);
        }   
    }
    return $conn;
}

function disconnect_db() {
    global $conn;
    if ($conn) {
        mysqli_close($conn);
    }
}

// Lấy thông tin khách hàng từ username (Dùng cho Index)
function get_info_nhanvien($username) {
    global $conn;
    connect_db();
    $sql = "SELECT nv_id, ten_nhan_vien FROM nhanvien WHERE username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $stmt->close();
    return $data; // Trả về mảng ['nv_id' => ..., 'ten_nhan_vien' => ...]
}
?>
<?php
$host = 'localhost';
$dbname = 'web_qlvt';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối Database: " . $e->getMessage());
}
?>
<?php
// Lấy thông tin từ Environment Variables đã cấu hình trên Azure
$host = getenv('DB_HOST') ?: 'qlvt.mysql.database.azure.com';
$db   = getenv('DB_NAME') ?: 'web_qlvt';
$user = getenv('DB_USER') ?: 'qlvt';
$pass = getenv('DB_PASS') ?: 'MAT_KHAU_MYSQL_CUA_BAN';
$port = 3306;

// Bắt buộc khai báo host dạng Domain/IP và kèm Port để PHP dùng TCP/IP
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Lỗi kết nối Database: " . $e->getMessage());
}
?>
