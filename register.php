<?php
// Kết nối cơ sở dữ liệu
include 'config/database.php';
$message = "";

// Lấy dữ liệu từ form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Kiểm tra xem username đã tồn tại chưa
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $message = "Tên đăng nhập đã tồn tại!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Truy vấn thêm người dùng vào CSDL
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
        }

        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            $message = "Đăng ký thành công!";
        } else {
            $message = "Có lỗi xảy ra khi đăng ký: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Đăng Ký</title>
    <link rel="stylesheet" href="../css/login_logout.css" type="text/css">
</head>
<body>
<div class="container">
    <form action="register.php" method="POST">
        <h2>Đăng Ký</h2>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <select name="role" required>
            <option value="" disabled selected>Chọn vai trò</option>
            <option value="user">Người dùng</option>
            <option value="admin">Quản trị viên</option>
        </select>
        <button type="submit">Đăng Ký</button>
        <p class="text_dangki"><a href="login.php">Đăng nhập ngay</a></p>
    </form>
</div>
</body>
</html>
