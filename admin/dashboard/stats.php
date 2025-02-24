<?php
session_start();
include_once '../../config/database.php';
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
// Ngày hiện tại
$current_date = date("Y-m-d");

// Truy vấn số công việc quá hạn
$query_overdue_tasks = "SELECT COUNT(taskid) AS overdue_tasks FROM tasks WHERE (status = 'Chưa hoàn thành' OR status = 'Đang gặp vấn đề') AND deadline < ?";
$stmt = $conn->prepare($query_overdue_tasks);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
$overdue_tasks = $result->fetch_assoc()['overdue_tasks'] ?? 0;

// Truy vấn danh sách công việc quá hạn
$query_overdue_list = "SELECT * FROM tasks WHERE (status = 'Chưa hoàn thành' OR status = 'Đang gặp vấn đề') AND deadline < ?";
$stmt = $conn->prepare($query_overdue_list);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
$overdue_list = $result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách người dùng
$usersQuery = "SELECT * FROM users"; 

// Tổng số công việc
$query_total_tasks = "SELECT COUNT(taskid) AS total_tasks FROM tasks";
$result = $conn->query($query_total_tasks);
$total_tasks = $result->fetch_assoc()['total_tasks'] ?? 0;

// Số công việc đã hoàn thành
$query_completed_tasks = "SELECT COUNT(taskid) AS completed_tasks FROM tasks WHERE status = 'Đã hoàn thành'";
$result = $conn->query($query_completed_tasks);
$completed_tasks = $result->fetch_assoc()['completed_tasks'] ?? 0;

// Số công việc đang xử lý
$query_in_progress_tasks = "SELECT COUNT(taskid) AS in_progress_tasks FROM tasks WHERE status = 'Chưa hoàn thành'";
$result = $conn->query($query_in_progress_tasks);
$in_progress_tasks = $result->fetch_assoc()['in_progress_tasks'] ?? 0;

// Số công việc đang gặp sự cố
$query_issue_tasks = "SELECT COUNT(taskid) AS issue_tasks FROM tasks WHERE status = 'Đang gặp vấn đề'";
$result = $conn->query($query_issue_tasks);
$issue_tasks = $result->fetch_assoc()['issue_tasks'] ?? 0;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Công Việc</title>
</head>
<body>

<h1>Dashboard Công Việc</h1>

<div class="dashboard-container">
    <div class="dashboard-box total-tasks">
        <h3>Tổng số công việc</h3>
        <p><?php echo $total_tasks; ?></p>
    </div>

    <div class="dashboard-box completed-tasks">
        <h3>Công việc đã hoàn thành</h3>
        <p><?php echo $completed_tasks; ?></p>
    </div>

    <div class="dashboard-box in-progress-tasks">
        <h3>Công việc đang xử lý</h3>
        <p><?php echo $in_progress_tasks; ?></p>
    </div>

    <div class="dashboard-box issue-tasks">
        <h3>Công việc gặp sự cố</h3>
        <p><?php echo $issue_tasks; ?></p>
    </div>
</div>
<div class="dashboard-box overdue-tasks">
    <h3>Công việc quá hạn</h3>
    <p><?php echo $overdue_tasks; ?></p>
</div>

<?php if ($overdue_tasks > 0): ?>
    <h2>Danh sách công việc quá hạn</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Hạn chót</th>
                <th>Trạng thái</th>
                <th>Phân công</th>
            
            </tr>
        </thead>
        <tbody>
            <?php foreach ($overdue_list as $task): ?>
                <tr>
                    <td><?php echo $task['taskid']; ?></td>
                    <td><?php echo $task['title']; ?></td>
                    <td><?php echo $task['deadline']; ?></td>
                    <td><?php echo $task['status']; ?></td>
                    <td><?php echo $task['assigned_username']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
<style>
.dashboard-box.overdue-tasks {
    background-color: #6c757d; /* Màu xám */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid black;
    text-align: center;
    padding: 8px;
}

th {
    background-color: #343a40;
    color: white;
}

td {
    background-color: #f8f9fa;
}

/* Container chính */
.dashboard-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 20px;
    background-color: #f8f9fa;
}

/* Ô thông tin */
.dashboard-box {
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    color: white;
    font-weight: bold;
    transition: transform 0.3s, box-shadow 0.3s;
}

/* Hiệu ứng hover */
.dashboard-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
}

/* Màu sắc cho từng loại công việc */
.dashboard-box.total-tasks {
    background-color: #007bff; /* Màu xanh dương */
}

.dashboard-box.completed-tasks {
    background-color: #28a745; /* Màu xanh lá */
}

.dashboard-box.in-progress-tasks {
    background-color: #ffc107; /* Màu vàng */
}

.dashboard-box.issue-tasks {
    background-color: #dc3545; /* Màu đỏ */
}

@media (max-width: 768px) {
    .dashboard-container {
        grid-template-columns: 1fr;
        padding: 10px;
    }
}

</style>