<?php
session_start(); // Khởi tạo session để lấy dữ liệu từ session

// Bao gồm header (CSS, Font Awesome)
include_once 'includes/header.php';

// Kết nối cơ sở dữ liệu
include_once 'config/database.php';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng</title>
    <!-- Thêm Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
 
<body>
<?php  
include_once 'includes/welcome.php';
 ?>
</body>
</html>