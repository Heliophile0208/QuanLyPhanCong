<?php
session_start();
include '../config/database.php';
include_once '../includes/header.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    die("Bạn cần đăng nhập để xem công việc.");
}

$username = $_SESSION['username']; // Lấy username từ session
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Công Việc</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        h2 {
            margin: 90px auto 10px auto;
            text-align: center;
            width: fit-content;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        td {
            background: #f9f9f9;
        }
        form {
            display: inline;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background: white;
            padding: 20px;
            width: 300px;
            margin: 10% auto;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
        }
        .close {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Danh Sách Công Việc</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Tiêu Đề</th>
        <th>Mô Tả</th>
         <th>Trạng Thái</th>
        <th>Ngày Tạo</th>
         <th>TÌnh Trạng</th>
        <th>Deadline</th>
        <th>Tệp Đính Kèm</th>
        <th>Hành Động</th>
    </tr>

    <?php
   $today = date('Y-m-d'); // Lấy ngày hiện tại
$sql = "SELECT * FROM tasks WHERE assigned_username = '$username'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $deadlineDate = date('Y-m-d', strtotime($row['deadline'])); // Chuyển deadline về định dạng so sánh
        $isLate = ($deadlineDate < $today && trim($row['status']) !== 'Đã hoàn thành'); // Trễ hạn nếu chưa hoàn thành

        // Xác định trạng thái
        $statusText = '';

        if (trim($row['status']) === 'Đã hoàn thành') {
            $statusText = 'Hoàn thành';
        } elseif (trim($row['status']) === 'Đang gặp vấn đề' && $isLate) {
            $statusText = 'Gặp vấn đề - Trễ hạn'; // Nếu gặp vấn đề và trễ hạn
        } elseif (trim($row['status']) === 'Đang gặp vấn đề') {
            $statusText = 'Gặp vấn đề';
        } elseif ($isLate) {
            $statusText = 'Trễ hạn';
        } else {
            $statusText = 'Còn hạn';
        }

        // Debug để kiểm tra dữ liệu có đúng không
        echo "<!-- DEBUG: TaskID=" . $row['taskid'] . " | Status=" . $row['status'] . " | isLate=" . ($isLate ? 'YES' : 'NO') . " -->";

        // Tô đỏ nếu trễ hạn
        $rowStyle = $isLate ? 'style="background: #ffcccc;"' : '';

        // Hiển thị dòng dữ liệu
        echo "<tr $rowStyle>";
        echo "<td>" . $row['taskid'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
        echo "<td>" . $statusText . "</td>"; 
        echo "<td>" . $row['created_at'] . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . $row['deadline'] . "</td>";

        // Nếu có file, chỉ lấy phần sau "uploads/"
        if (!empty($row['file'])) {
            $filePath = $row['file'];
            $relativePath = strstr($filePath, "uploads/");

            if ($relativePath !== false) {
                echo "<td><a href='/" . htmlspecialchars($relativePath) . "' target='_blank'>Tải về</a></td>";
            } else {
                echo "<td>Lỗi đường dẫn</td>";
            }
        } else {
            echo "<td>Không có</td>";
        }

        echo "<td><button class='update-btn' data-taskid='" . $row['taskid'] . "' data-status='" . htmlspecialchars($row['status']) . "'>Cập Nhật</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8'>Không có công việc nào.</td></tr>";
}

    ?>
</table>

<!-- Modal -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Cập Nhật Trạng Thái</h3>
        <form id="updateTaskForm" action="update_task.php" method="POST">
            <input type="hidden" name="task_id" id="task_id">
            <label for="status">Chọn Trạng Thái:</label>
            <select name="status" id="status">
                <option value="Chưa hoàn thành">Chưa hoàn thành</option>
                <option value="Đang gặp vấn đề">Đang gặp vấn đề</option>
                <option value="Đã hoàn thành">Đã hoàn thành</option>
            </select>
            <button type="submit">Lưu</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById("taskModal");
    const closeModal = document.querySelector(".close");
    const updateButtons = document.querySelectorAll(".update-btn");
    const taskInput = document.getElementById("task_id");
    const statusSelect = document.getElementById("status");

    updateButtons.forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
            taskInput.value = this.dataset.taskid;
            statusSelect.value = this.dataset.status;
            modal.style.display = "block";
        });
    });

    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
});
</script>

</body>
</html>
