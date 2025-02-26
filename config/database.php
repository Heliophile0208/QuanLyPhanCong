
<?php
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "rbgdcnwyhosting_manager";
$password = "Khanh@123"; 
$dbname = "rbgdcnwyhosting_manager"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
// Đặt bộ mã hóa UTF-8
$conn->set_charset("utf8mb4");
$conn->query("SET NAMES 'utf8mb4'");
$conn->query("SET CHARACTER SET utf8mb4");
$conn->query("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
?>