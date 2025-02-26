<?php session_start();
include_once 'header.php'; // Kết nối header và CSS
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng Dẫn Sử Dụng Hệ Thống Quản Lý Công Việc</title>

</head>
<body>
    <div class="container">
        <h1>Hướng Dẫn Sử Dụng Hệ Thống Quản Lý Công Việc</h1>
        
        <h2>1. Giới Thiệu</h2>
        <p>Hệ thống Quản lý Công việc giúp theo dõi, tổ chức và quản lý tiến độ công việc hiệu quả...</p>
        
        <h2>2. Đăng Nhập Hệ Thống</h2>
        <ul>
            <li>Truy cập trang web <a href="https://nidtech.vn">nidtech.vn</a>.</li>
            <li>Nhập <strong>tên đăng nhập</strong> và <strong>mật khẩu</strong>.</li>
            <li>Nhấn <strong>Đăng nhập</strong> để vào hệ thống.</li>
        </ul>
        
        <h2>3. Quản Lý Công Việc</h2>
        <h3>3.1. Tạo Công Việc Mới</h3>
        <ul>
            <li>Chuyển đến menu <strong>Công việc</strong>.</li>
            <li>Nhấn <strong>Thêm công việc</strong>.</li>
            <li>Nhập thông tin cần thiết và nhấn <strong>Lưu</strong>.</li>
        </ul>
        
        <h3>3.2. Cập Nhật Trạng Thái Công Việc</h3>
        <ul>
            <li>Chọn công việc cần cập nhật.</li>
            <li>Thay đổi trạng thái: <strong>Chưa bắt đầu, Đang thực hiện, Hoàn thành</strong>.</li>
            <li>Nhấn <strong>Lưu</strong>.</li>
        </ul>
        
        <h2>4. Thống Kê & Báo Cáo</h2>
        <p>Xem danh sách công việc theo người thực hiện, trạng thái, hạn chót.</p>
        
        <h2>5. Quản Lý Tài Khoản</h2>
        <ul>
            <li>Đổi mật khẩu trong phần <strong>Cài đặt</strong>.</li>
            <li>Cập nhật thông tin cá nhân trong <strong>Hồ sơ</strong>.</li>
        </ul>
        
        <h2>6. Hỗ Trợ Kỹ Thuật</h2>
        <p>Email: <a href="mailto:nidtech.vn@gmail.com"> nidtech.vn@gmail.com</a></p>
        <p>Hotline:<a href="tel:0947228844"> 0947.22.88.44</a></p>
    </div>
</body>
</html>

<style>


.container {
    max-width: 90%;
    margin: 35px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 20px;
}

h2 {
    color: #2980b9;
    margin-top: 20px;
}

h3 {
    color: #27ae60;
    margin-top: 15px;
}

ul {
    list-style: none;
    padding-left: 20px;
}

ul li {
    padding: 5px 0;
    position: relative;
}


a {
    color: #3498db;
    text-decoration: none;
}


p {
    margin-bottom: 10px;
}

@media (max-width: 600px) {
    .container {
        padding: 15px;
    }
}

</style>