<?php
session_start();
include_once '../config/database.php';
include_once '../includes/header.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit;
}

$username = $_SESSION['username'];

// Truy vấn để lấy user_id và role từ bảng users
$query_user = "SELECT id, role FROM users WHERE username = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user_row = $result_user->fetch_assoc();
    $user_id = $user_row['id'];
    $role = $user_row['role'];

    // Nếu role không phải admin thì chuyển hướng về login
    if ($role !== 'admin') {
        header("Location: /login.php");
        exit;
    }
} else {
    header("Location: /login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Giữ nguyên CSS cũ */
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
    transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
}

.sidebar ul li a:hover,
.sidebar ul li a.active {
    background-color: #007bff;
    color: #fff;
    transform: scale(1.05);
}

    
    .dashboard {
        flex: 1;
        padding: 30px;
    }
    
    #dashboard-content {
        margin-top: 20px;
        text-align: center;
    }
    
    .sidebar ul li a.active {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }
.parent-menu {
    margin-bottom: 10px; /* Khoảng cách bên dưới */
    padding-bottom: 5px;
    position: relative; /* Đảm bảo phần tử cha chứa phần tử con */
    display: flex;
    justify-content: space-between; /* Đưa icon về góc phải */
    align-items: center;
    padding-right: 15px; /* Tạo khoảng cách với mép phải */
}

.parent-menu:after {
    content: "\25BC"; /* Unicode mũi tên xuống */
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    transition: transform 0.3s ease;
}

/* Khi tiêu đề cha active thì xoay icon mũi tên */
.parent-menu.active:after {
    transform: translateY(-50%) rotate(180deg);
}
    /* CSS bổ sung cho sub-menu của accordion */
    .sub-menu {
        margin-left: 10px;
        display: none;
    }
  </style>
</head>
<body>
  <div class="container-dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
      <h2>Quản Lý</h2>
      <ul>
        <!-- Mục Tổng quan -->
        <li><a href="#" id="stats">Tổng quan</a></li>
        
        <!-- Accordion: Nhân viên -->
        <li>
          <a href="#" class="parent-menu" id="nhanvien-toggle">Nhân viên</a>
          <ul class="sub-menu">
            <li><a href="#" id="profile">Thông tin</a></li>
            <li><a href="#" id="users">Người dùng</a></li>
          </ul>
        </li>
        
        <!-- Accordion: Công việc -->
        <li>
          <a href="#" class="parent-menu" id="congviec-toggle">Công việc</a>
          <ul class="sub-menu">
            <li><a href="#" id="works">Giao việc</a></li>
            <li><a href="#" id="process">Tiến độ</a></li>
          </ul>
        </li>
        
        <!-- Accordion: Công ty -->
        <li>
          <a href="#" class="parent-menu" id="congty-toggle">Công ty</a>
          <ul class="sub-menu">
            <li><a href="#" id="categories">Danh mục</a></li>
            <li><a href="#" id="positions">Chức vụ</a></li>
            <li><a href="#" id="departments">Phòng ban</a></li>
          </ul>
        </li>
      </ul>
    </div>
    
    <!-- Nội dung chính -->
    <div class="dashboard">
      <div id="dashboard-content">
        <h1>Chào mừng <?php echo $username; ?> đến trang quản lý</h1>
        <h2>Vui lòng chọn một mục từ sidebar để hiển thị dữ liệu</h2>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function(){
      // Toggle accordion Nhân viên
      $("#nhanvien-toggle").click(function(e){
        e.preventDefault();
        $(this).next(".sub-menu").slideToggle();
      });
      
      // Toggle accordion Công việc
      $("#congviec-toggle").click(function(e){
        e.preventDefault();
        $(this).next(".sub-menu").slideToggle();
      });
      
      // Toggle accordion Công ty
      $("#congty-toggle").click(function(e){
        e.preventDefault();
        $(this).next(".sub-menu").slideToggle();
      });

      // Các sự kiện click load nội dung qua AJAX
      $('#stats').click(function() {
        $('#dashboard-content').load('dashboard/stats.php');
      });
      $('#profile').click(function() {
        $('#dashboard-content').load('dashboard/profile/profile.php');
      });
      $('#users').click(function() {
        $('#dashboard-content').load('dashboard/users/users.php');
      });
      $('#works').click(function() {
        $('#dashboard-content').load('dashboard/tasks/tasks.php');
      });
      $('#process').click(function() {
        $('#dashboard-content').load('dashboard/all.php');
      });
      $('#categories').click(function() {
        $('#dashboard-content').load('dashboard/categories/categories.php');
      });
      $('#positions').click(function() {
        $('#dashboard-content').load('dashboard/positions/positions.php');
      });
      $('#departments').click(function() {
        $('#dashboard-content').load('dashboard/departments/departments.php');
      });

     // Đánh dấu active cho mục được click và thiết lập active cho parent nếu là mục con
      $('.sidebar ul li a').click(function(e){
        e.preventDefault();
        // Xóa active khỏi tất cả các mục
        $('.sidebar ul li a').removeClass('active');
        // Thêm active cho mục được click
        $(this).addClass('active');
        
        // Nếu mục được click là mục con, thêm active cho parent-menu của nó
        if($(this).closest('.sub-menu').length > 0){
          $(this).closest('.sub-menu').prev('.parent-menu').addClass('active');
        }
        
        // Load nội dung nếu có thuộc tính id và không phải là mục accordion cha
        var page = $(this).attr('id');
        if(page && !$(this).hasClass('parent-menu')){
          $('#dashboard-content').load('dashboard/' + page + '/' + page + '.php');
        }
      });
    });
  </script>
</body>
</html>
