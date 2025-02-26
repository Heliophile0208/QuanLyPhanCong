<?php
session_start();
include_once '../../config/database.php';

if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit;
}

// Hàm lấy hình ảnh tương ứng với tiến độ
function getProgressImage($progress) {
    if ($progress >= 100) return "../../../images/4.png";
    if ($progress >= 75) return "../../../images/3.png";
    if ($progress >= 50) return "../../../images/2.png";
    if ($progress >= 25) return "../../../images/1.png";
    return "../../../images/0.png";
}

// Lấy từ khóa tìm kiếm (nếu có) từ POST
$search_name = "";
if (isset($_POST['submit_search'])) {
    $search_name = trim($_POST['search']);
}

// Lấy danh sách người dùng theo điều kiện tìm kiếm
if (!empty($search_name)) {
    $query_users = "SELECT username FROM users WHERE username LIKE ?";
    $stmt_users = $conn->prepare($query_users);
    $like_search = "%" . $search_name . "%";
    $stmt_users->bind_param("s", $like_search);
    $stmt_users->execute();
    $result_users = $stmt_users->get_result();
    $stmt_users->close();
} else {
    $query_users = "SELECT username FROM users";
    $result_users = $conn->query($query_users);
}

$progress_data = [];
$current_date = date("Y-m-d");

// Tính tiến độ cho mỗi người dùng
while ($user = $result_users->fetch_assoc()) {
    $username = $user['username'];
    
    // Lấy tổng số công việc của người dùng
    $query_total = "SELECT COUNT(taskid) AS total FROM tasks WHERE assigned_username = ?";
    $stmt_total = $conn->prepare($query_total);
    $stmt_total->bind_param("s", $username);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_tasks = $row_total['total'] ?? 0;
    $stmt_total->close();
    
    if ($total_tasks > 0) {
        // Lấy danh sách công việc của người dùng
        $query_tasks = "SELECT status, deadline FROM tasks WHERE assigned_username = ?";
        $stmt_tasks = $conn->prepare($query_tasks);
        $stmt_tasks->bind_param("s", $username);
        $stmt_tasks->execute();
        $result_tasks = $stmt_tasks->get_result();
        
        $total_points = 0;
        while ($task = $result_tasks->fetch_assoc()) {
            $status = $task['status'];
            $deadline = $task['deadline'];
            // Kiểm tra công việc có trễ hạn không
            $is_overdue = strtotime($deadline) < strtotime($current_date);
            
            if ($is_overdue) { // Công việc trễ hạn
                if ($status == 'Đã hoàn thành') {
                    $task_progress = 100;
                } elseif ($status == 'Chưa hoàn thành') {
                    $task_progress = 30;
                } elseif ($status == 'Đang gặp vấn đề') {
                    $task_progress = 10;
                } else {
                    $task_progress = 0;
                }
            } else { // Không trễ hạn
                if ($status == 'Đã hoàn thành') {
                    $task_progress = 100;
                } elseif ($status == 'Chưa hoàn thành') {
                    $task_progress = 70;
                } elseif ($status == 'Đang gặp vấn đề') {
                    $task_progress = 40;
                } else {
                    $task_progress = 0;
                }
            }
            
            $total_points += $task_progress;
        }
        $stmt_tasks->close();
        
        // Tính trung bình tiến độ của nhân viên
        $average_progress = $total_tasks > 0 ? $total_points / $total_tasks : 0;
        
        // Lấy số lượng công việc theo từng trạng thái
        
        // Hoàn thành
        $query_completed = "SELECT COUNT(taskid) as completed FROM tasks WHERE assigned_username = ? AND status = 'Đã hoàn thành'";
        $stmt_completed = $conn->prepare($query_completed);
        $stmt_completed->bind_param("s", $username);
        $stmt_completed->execute();
        $result_completed = $stmt_completed->get_result();
        $row_completed = $result_completed->fetch_assoc();
        $completed = $row_completed['completed'] ?? 0;
        $stmt_completed->close();
        
        // Chưa hoàn thành
        $query_not_completed = "SELECT COUNT(taskid) as not_completed FROM tasks WHERE assigned_username = ? AND status = 'Chưa hoàn thành'";
        $stmt_not_completed = $conn->prepare($query_not_completed);
        $stmt_not_completed->bind_param("s", $username);
        $stmt_not_completed->execute();
        $result_not_completed = $stmt_not_completed->get_result();
        $row_not_completed = $result_not_completed->fetch_assoc();
        $not_completed = $row_not_completed['not_completed'] ?? 0;
        $stmt_not_completed->close();
        
        // Đang gặp vấn đề
        $query_issues = "SELECT COUNT(taskid) as issues FROM tasks WHERE assigned_username = ? AND status = 'Đang gặp vấn đề'";
        $stmt_issues = $conn->prepare($query_issues);
        $stmt_issues->bind_param("s", $username);
        $stmt_issues->execute();
        $result_issues = $stmt_issues->get_result();
        $row_issues = $result_issues->fetch_assoc();
        $issues = $row_issues['issues'] ?? 0;
        $stmt_issues->close();
        
    } else {
        $average_progress = 0;
        $completed = 0;
        $not_completed = 0;
        $issues = 0;
    }
    
    $progress_data[] = [
        'username'      => $username,
        'total'         => $total_tasks,
        'progress'      => round($average_progress, 2),
        'completed'     => $completed,
        'not_completed' => $not_completed,
        'issues'        => $issues
    ];
}

// Nếu đây là yêu cầu AJAX, chỉ trả về nội dung bảng để cập nhật
if (isset($_POST['ajax']) && $_POST['ajax'] == 'true') {
    ?>
    <div id="progress-table">
        <table>
            <thead>
                <tr>
                    <th>Tên người dùng</th>
                    <th>Tổng công việc</th>
                    <th>Hoàn thành</th>
                    <th>Chưa hoàn thành</th>
                    <th>Đang gặp vấn đề</th>
                    <th>Tiến độ trung bình</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($progress_data as $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($data['username']); ?></td>
                        <td><?php echo $data['total']; ?></td>
                        <td><?php echo $data['completed']; ?></td>
                        <td><?php echo $data['not_completed']; ?></td>
                        <td><?php echo $data['issues']; ?></td>
                        <td>
                            <img class="task-progress" src="<?php echo getProgressImage($data['progress']); ?>" alt="Tiến độ: <?php echo $data['progress']; ?>%" width="70">
                            <br>
                            <span class="
                                progress-text 
                                <?php 
                                    if ($data['progress'] >= 75) echo 'progress-high'; 
                                    elseif ($data['progress'] >= 50) echo 'progress-medium'; 
                                    elseif ($data['progress'] >= 25) echo 'progress-low'; 
                                    else echo 'progress-very-low'; 
                                ?>
                            ">
                                <?php echo $data['progress']; ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tiến độ công việc</title>
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:hover td {
            background-color: #f1f1f1;
            transition: 0.3s;
        }
        .progress-text {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            width: 80px;
            text-align: center;
        }
        .progress-very-low {
            color: #dc3545;
            
        }
        .progress-low {
           color: #fd7e14;
        
        }
        .progress-medium {
           color: #ffc107;
           
        }
        .progress-high {
            color: #28a745;
            
        }
        img.task-progress {
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        /* Style cho thanh tìm kiếm */
        .search-container {
            text-align: center;
            margin: 20px;
        }
        .search-container input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-container button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .search-container button:hover {
            background-color: #45a049;
        }
    </style>
    <!-- Bao gồm jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1 style="text-align:center;">Tiến độ công việc theo người dùng</h1>
    
    <!-- Thanh tìm kiếm -->
    <div class="search-container">
        <form id="search-form" method="POST">
            <input type="text" name="search" placeholder="Tìm kiếm theo người được phân công" value="<?php echo htmlspecialchars($search_name); ?>">
            <button type="submit" name="submit_search">Tìm kiếm</button>
        </form>
    </div>
    
    <!-- Bảng hiển thị tiến độ -->
    <div id="progress-table">
        <table>
            <thead>
                <tr>
                    <th>Tên người dùng</th>
                    <th>Tổng công việc</th>
                    <th>Hoàn thành</th>
                    <th>Chưa hoàn thành</th>
                    <th>Đang gặp vấn đề</th>
                    <th>Tiến độ trung bình</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($progress_data as $data): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($data['username']); ?></td>
                        <td><?php echo $data['total']; ?></td>
                        <td><?php echo $data['completed']; ?></td>
                        <td><?php echo $data['not_completed']; ?></td>
                        <td><?php echo $data['issues']; ?></td>
                        <td>
                            <img class="task-progress" src="<?php echo getProgressImage($data['progress']); ?>" alt="Tiến độ: <?php echo $data['progress']; ?>%" width="50">
                            <br>
                            <span class="
                                progress-text 
                                <?php 
                                    if ($data['progress'] >= 75) echo 'progress-high'; 
                                    elseif ($data['progress'] >= 50) echo 'progress-medium'; 
                                    elseif ($data['progress'] >= 25) echo 'progress-low'; 
                                    else echo 'progress-very-low'; 
                                ?>
                            ">
                                <?php echo $data['progress']; ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- AJAX: Gửi yêu cầu tìm kiếm và cập nhật bảng tiến độ -->
    <script>
    $(document).ready(function(){
        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngăn gửi form mặc định
            
            var searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/all.php',  // Đảm bảo đường dẫn đúng tới file này
                method: 'POST',
                data: { submit_search: true, search: searchTerm, ajax: 'true' },
                success: function(response) {
                    // Cập nhật nội dung bảng tiến độ
                    $('#progress-table').html(response);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });
        });
    });
    </script>
</body>
</html>
