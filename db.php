
<?php
// FILE: db.php
// Bản sửa: gộp về 1 khối PHP duy nhất, không còn dòng trống hay thẻ đóng nằm
// giữa file - đó là nguyên nhân gây lỗi "headers already sent" trước đây.
// Không dùng thẻ đóng ở cuối file để tránh xuất ký tự thừa ra trình duyệt.
 
global $conn;
 
// --- Kết nối mysqli (giữ lại để tương thích ngược, KHÔNG tự chạy khi include file) ---
function connect_db()
{
    global $conn;
    if ($conn === null) {
        $conn = new mysqli('localhost', 'root', '', 'web_qlvt');
        if ($conn->connect_errno) {
            die("Loi ket noi DB: (" . $conn->connect_errno . ") " . $conn->connect_error);
        }
        mysqli_set_charset($conn, 'utf8');
    }
    return $conn;
}
 
function disconnect_db()
{
    global $conn;
    if ($conn instanceof mysqli) {
        mysqli_close($conn);
    }
}
 
// Lấy thông tin nhân viên từ username (dùng mysqli, hiện chưa nơi nào gọi hàm này)
function get_info_nhanvien($username)
{
    global $conn;
    if (!($conn instanceof mysqli)) {
        connect_db();
    }
    $sql = "SELECT nv_id, ten_nhan_vien FROM nhanvien WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}
 
// --- Kết nối PDO: đây là kết nối CHÍNH mà toàn bộ web đang dùng qua biến $conn ---
// Ưu tiên lấy thông tin kết nối từ biến môi trường (App Settings trên Azure),
// nếu không có thì dùng mặc định của XAMPP (localhost/root/rỗng) để chạy local như cũ.
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'web_qlvt';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
 
try {
    $conn = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Loi ket noi Database: " . $e->getMessage());
}
