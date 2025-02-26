
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
<link rel="icon" type="image/png" href="../images/nidtech.png">

   <!-- Thêm liên kết đến Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&family=Lobster&display=swap" rel="stylesheet">

 <title>Trang Quản Lý</title>
    
</head>
<style>
/* Tổng thể header */
header {
    background-color: #ffffff; /* Màu nền */
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1); /* Đổ bóng nhẹ */
    padding: 10px 0;
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
}

/* Chứa header */
.container-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0px 10px;
    max-width: 1200px;
    margin: -10px auto;
}

/* Logo */
.logo img {
    height: 60px; /* Giới hạn chiều cao logo */
    width: auto;
}

/* Menu điều hướng */
nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
}

nav ul li {
    margin: 0 10px;
    position: relative;
}

nav ul li a {
    text-decoration: none;
    color: #333;
    font-size: 18px;
    font-weight: bold;
    padding: 5px 15px;
    transition: color 0.3s ease-in-out;
}

nav ul li a:hover {
    color: #007bff;
}

/* Dropdown */
.dropdown .dropbtn {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    padding: 10px 15px;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
    min-width: 180px;
    top: 100%;
    left: 0;
    border-radius: 5px;
}

.dropdown-content a {
    color: #333;
    padding: 10px 15px;
    display: block;
    transition: background 0.3s;
}

.dropdown-content a:hover {
    background-color: #f5f5f5;
}

.dropdown:hover .dropdown-content {
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .container-header {
        flex-direction: column;
        align-items: center;
    }

    nav ul {
        flex-direction: column;
        text-align: center;
    }

    nav ul li {
        margin: 5px 0;
    }
}
/* Mục đang active */
nav ul li a.active,
nav ul li a:hover {
    color: #007bff;
    border-bottom: 2px solid #007bff; /* Hiệu ứng gạch chân */
    transition: all 0.3s ease-in-out;
}



</style>
<body>

<header>
    <div class="container-header">
<div class="logo">
    <a style=" text-decoration: none; color: black;" href="/index.php">
        <img src="https://nidtech.vn/wp-content/uploads/2024/11/logo-xoa-phon.png" width="190px" height="auto" alt="NIDTECH Logo">
    </a></div>
        <nav>
            <ul >
          <li><a href="/includes/about.php" class="<?= ($_SERVER['REQUEST_URI'] == '/includes/about.php') ? 'active' : '' ?>">Giới Thiệu</a></li>
<li><a href="/includes/huongdansudung.php" class="<?= ($_SERVER['REQUEST_URI'] == '/includes/huongdansudung.php') ? 'active' : '' ?>">Hướng Dẫn Sử Dụng</a></li>
<li><a href="/includes/contact.php" class="<?= ($_SERVER['REQUEST_URI'] == '/includes/contact.php') ? 'active' : '' ?>">Liên Hệ</a></li>


                <?php if (isset($_SESSION['username'])): ?>
                    <!-- Dropdown cho người dùng bình thường -->
                    <?php if ($_SESSION['role'] === 'user'): ?>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Chào mừng, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                            <div class="dropdown-content">
                                <a href="/user/profile.php">Trang Cá Nhân</a>
                                <a href="/user/tasks.php">Công Việc</a>
                                <a href="/logout.php">Đăng Xuất</a>
                            </div>
                        </li>
                    <?php endif; ?>

                    <!-- Dropdown cho quản trị viên -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Quản Trị</a>
                            <div class="dropdown-content">
                                <a href="/admin/dashboard.php">Dashboard</a>

                                <a href="/logout.php">Đăng Xuất</a>
                            </div>
                        </li>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Hiển thị liên kết Đăng Nhập nếu chưa đăng nhập -->
                    <li><a href="/login.php">Đăng Nhập</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

</body>
</html>
