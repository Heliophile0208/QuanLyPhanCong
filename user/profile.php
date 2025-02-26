<?php session_start();  include '../includes/header.php' ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Nhân Viên</title>
</head>
<style>

/* Khung chứa nội dung */
.khungbao {
    width: 90%;
    max-width: 800px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin:90px auto;
}

/* Tiêu đề */
h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 15px;
}

/* Bảng thông tin nhân viên */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    background: white;
}

/* Định dạng tiêu đề và nội dung bảng */
th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

/* Tiêu đề bảng */
th {
    background: #007bff;
    color: white;
    font-weight: bold;
}

/* Dòng chẵn có màu nền khác để dễ nhìn */
tr:nth-child(even) {
    background: #f9f9f9;
}

/* Nút cập nhật */
.btn {
    display: inline-block;
    padding: 12px 20px;
    margin-top: 15px;
    font-size: 16px;
    color: white;
    background: #007bff;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.3s ease;
}

.btn:hover {
    background: #0056b3;
}

/* Responsive - Điều chỉnh kích thước trên màn hình nhỏ */
@media (max-width: 600px) {
    th, td {
        padding: 10px;
        font-size: 14px;
    }

    .btn {
        width: 100%;
    }
}

</style>
<body>
<div class="khungbao">
    <div class="container-profile">
        <?php
        // Include file kết nối database
        include '../config/database.php';

        // Lấy username từ session
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

        if (!$username) {
            die("<p>Bạn cần đăng nhập để xem thông tin.</p>");
        }

        // Truy vấn user_id từ bảng users
        $sql = "SELECT id FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $user_id = $row['id'];

            // Lấy thông tin nhân viên từ bảng employees
            $employee_sql = "
                SELECT id, employee_name, employee_email, phone_number, position, address, city, state, postal_code, country, department_id
                FROM employees
                WHERE user_id = $user_id
            ";
            $employee_result = mysqli_query($conn, $employee_sql);

            if (mysqli_num_rows($employee_result) > 0) {
                $employee = mysqli_fetch_assoc($employee_result);

                // Hiển thị thông tin nhân viên dạng bảng
                echo "<h2>Thông tin nhân viên</h2>";
                echo "<table>
                        <tr><th>Thông tin</th><th>Chi tiết</th></tr>
                        <tr><td>Tên nhân viên</td><td>" . htmlspecialchars($employee['employee_name']) . "</td></tr>
                        <tr><td>Email</td><td>" . htmlspecialchars($employee['employee_email']) . "</td></tr>
                        <tr><td>Số điện thoại</td><td>" . htmlspecialchars($employee['phone_number']) . "</td></tr>
                        <tr><td>Chức vụ</td><td>" . htmlspecialchars($employee['position']) . "</td></tr>
                        <tr><td>Địa chỉ</td><td>" . htmlspecialchars($employee['address']) . "</td></tr>
                        <tr><td>Thành phố</td><td>" . htmlspecialchars($employee['city']) . "</td></tr>
                        <tr><td>Quốc gia</td><td>" . htmlspecialchars($employee['country']) . "</td></tr>
                        <tr><td>Mã phòng ban</td><td>" . htmlspecialchars($employee['department_id']) . "</td></tr>
                      </table>";
                
                // Nút Cập nhật thông tin
                echo '<a href="/user/update_employee.php" class="btn">Cập nhật thông tin</a>';
            } else {
                echo "<p>Không tìm thấy thông tin nhân viên.</p>";
                echo '<a href="/user/update_employee.php" class="btn">Vui lòng cập nhật thông tin</a>';
            }
        } else {
            echo "<p>Không tìm thấy người dùng.</p>";
        }

        // Đóng kết nối
        mysqli_close($conn);
        ?>
    </div>
</div>
</body>
</html>