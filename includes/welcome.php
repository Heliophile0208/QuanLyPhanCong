<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php"); // Chuyển về trang đăng nhập nếu chưa đăng nhập
    exit();
}

// Chuyển hướng dựa vào vai trò
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
} elseif ($_SESSION['role'] === 'user') {
    header("Location: user/tasks.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng - NIDTECH</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
</head>
<style>
body {
    font-family: 'Poppins', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(135deg, #007bff, #00c6ff);
    margin: 0;
}

.welcome-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    text-align: center;
    max-width: 400px;
}

h1 {
    font-size: 24px;
    color: #333;
}

p {
    font-size: 16px;
    color: #666;
}

.btn {
    display: inline-block;
    margin: 10px;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: 0.3s;
}

.btn:hover {
    background: #0056b3;
}

.logout {
    background: red;
}

.logout:hover {
    background: darkred;
}

</style>
<body>

    <div class="welcome-container">
        <h1>Chào mừng, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Hệ thống quản lý công việc chuyên nghiệp tại NIDTECH.</p>

        <!-- Nút chuyển hướng -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin/dashboard.php" class="btn">Vào Dashboard</a>
        <?php else: ?>
            <a href="user/tasks.php" class="btn">Vào Công Việc</a>
        <?php endif; ?>

        <a href="logout.php" class="btn logout">Đăng xuất</a>
    </div>

</body>
</html>
