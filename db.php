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
// Lấy cấu hình từ biến môi trường Azure (hoặc dùng giá trị Azure MySQL)
$host     = getenv('DB_HOST') ?: 'qlvt.mysql.database.azure.com';
$dbname   = getenv('DB_NAME') ?: 'web_qlvt';
$username = getenv('DB_USER') ?: 'qlvt';
$password = getenv('DB_PASS') ?: ''; // Nhập mật khẩu MySQL Azure của bạn vào đây nếu chưa cài biến môi trường
$port     = 3306;

// 1. Cấu hình PDO (Cho phần truy vấn PDO)
try {
    // Bắt buộc có port=3306 và host dạng domain để ép PHP kết nối qua TCP/IP
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Lỗi kết nối Database (PDO): " . $e->getMessage());
}

// 2. Cấu hình mysqli (Cho các hàm connect_db cũ)
$mysqli_conn = null;

function connect_db() {
    global $mysqli_conn, $host, $username, $password, $dbname, $port;
    if ($mysqli_conn === null) {
        // Dùng mysqli_init để tạo kết nối TCP/IP đến Azure
        $mysqli_conn = mysqli_init();
        if (!$mysqli_conn->real_connect($host, $username, $password, $dbname, $port)) {
            die("Lỗi kết nối DB (mysqli): (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
        }
        mysqli_set_charset($mysqli_conn, 'utf8mb4');
    }
    return $mysqli_conn;
}

function disconnect_db() {
    global $mysqli_conn;
    if ($mysqli_conn) {
        mysqli_close($mysqli_conn);
        $mysqli_conn = null;
    }
}

// Lấy thông tin nhân viên từ username
function get_info_nhanvien($username) {
    $conn_mysqli = connect_db();
    $sql = "SELECT nv_id, ten_nhan_vien FROM nhanvien WHERE username = ?";

    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $stmt->close();
    return $data;
}
?>

