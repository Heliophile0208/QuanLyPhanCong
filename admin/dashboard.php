<?php
session_start();
include_once '../config/database.php';
include_once '../includes/header.php'; // Đảm bảo kết nối cơ sở dữ liệu

// Kiểm tra nếu chưa đăng nhập thì chuyển hướng về login
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit; // Dừng script sau khi chuyển hướng
}

// Lấy username từ session
$username = $_SESSION['username'];

// Truy vấn để lấy user_id và role từ bảng users
$query_user = "SELECT id, role FROM users WHERE username = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    // Lấy dữ liệu user
    $user_row = $result_user->fetch_assoc();
    $user_id = $user_row['id'];
    $role = $user_row['role'];

    // Kiểm tra nếu role không phải admin thì chuyển hướng về login
    if ($role !== 'admin') {
        header("Location: /login.php");
        exit;
    }
} else {
    // Không tìm thấy user, chuyển hướng về login
    header("Location: /login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
/* dashboard.css */
.container-dashboard {
    display: flex;
    padding: 10px;
    margin-top: 20px;
}

.sidebar {
    width: 12%;
    background-color: #f8f9fa;
    padding: 40px;
    border-right: 2px solid #ccc;
    height: 100vh;
}

.sidebar h2 {
    font-size: 24px;
    margin-bottom: 20px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    margin-bottom: 15px;
}

.sidebar ul li a {
    text-decoration: none;
    color: #333;
    padding: 10px;
    display: block;
    transition: background-color 0.3s;
}

.sidebar ul li a:hover, .sidebar ul li a.active {
    background-color: #007bff;
    color: white;
}

.dashboard {
    flex: 1;
    padding: 30px;
}

#dashboard-content {
    margin-top: 20px;
    text-align :center;
}
.sidebar ul li a.active {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

</style>
<body>

<?php if (isset($message_error)) : ?>
    <div style="color: red;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 5px;
    font-size:20px;
    text-align:center;
    border-radius: 5px;" id="error-message">
        <p><?php echo $message_error; ?></p>
    </div>

    <script>
        // Hiển thị thông báo
        setTimeout(function() {
            document.getElementById('error-message').style.display = 'none';
        }, 2000); 
    </script>
<?php endif; ?>
    <div class="container-dashboard">
        <!-- Sidebar for navigation -->
        <div class="sidebar">
            <h2>Quản Lý</h2>
            <ul>
                <li><a href="#" id="stats">Tổng quan</a></li>
                <li><a href="#" id="profile">Thông tin tài khoản</a></li>
                <li><a href="#" id="works">Giao việc</a></li>
                <li><a href="#" id="users">Người dùng</a></li>
                <li><a href="#" id="categories">Danh mục</a></li>
                <li><a href="#" id="departments">Phòng ban</a></li>
                                <li><a href="#" id="positions">Vị trí</a></li>
     
            </ul>
        </div>

        <!-- Dashboard content -->
        <div class="dashboard">
         

            <div id="dashboard-content">
       <h1> Chào mừng <?php echo $username; ?> đến trang quản lý</h1>

                <h2>Vui lòng chọn một mục từ sidebar để hiển thị dữ liệu</h2>


            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Lắng nghe sự kiện click vào các mục trong sidebar
            $('#stats').click(function() {
                $('#dashboard-content').load('dashboard/stats.php'); // Tải nội dung của trang stats.php vào div
            });


            $('#profile').click(function() {
                $('#dashboard-content').load('dashboard/profile/profile.php'); // Tải nội dung của trang info_account.php vào div
            });

            $('#works').click(function() {
                $('#dashboard-content').load('dashboard/tasks/tasks.php'); // Tải danh sách sản phẩm vào div
            });


            $('#users').click(function() {
                $('#dashboard-content').load('dashboard/users/users.php'); // Tải danh sách người dùng vào div
            });

            $('#categories').click(function() {
                $('#dashboard-content').load('dashboard/categories/categories.php'); // Tải danh mục việc làm vào div
            });
                        $('#departments').click(function() {
                $('#dashboard-content').load('dashboard/departments/departments.php'); // Tải danh mục việc làm vào div
            });
                 $('#positions').click(function() {
                $('#dashboard-content').load('dashboard/positions/positions.php'); // Tải danh mục 
            });



        });
        $(document).ready(function() {
    // Khi click vào mục trong sidebar
    $('.sidebar ul li a').click(function() {
        // Xóa class active khỏi tất cả các mục
        $('.sidebar ul li a').removeClass('active');
        // Thêm class active vào mục vừa click
        $(this).addClass('active');

        // Lấy đường dẫn và tải nội dung vào dashboard-content
        var page = $(this).attr('id');
        $('#dashboard-content').load('dashboard/' + page + '/' + page + '.php');
    });
});

    </script>
</body>
</html>