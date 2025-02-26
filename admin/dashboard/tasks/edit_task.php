
<style>
     
    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    label {
        font-size: 14px;
        margin-bottom: 5px;
        display: block;
        font-weight: bold;
    }

    input[type="text"],
    input[type="password"],
    select,
    button {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    select:focus {
        border-color: #4CAF50;
    }

    button {
        background-color: #4CAF50;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border: none;
    }

    button:hover {
        background-color: #45a049;
    }

    button:disabled {
        background-color: #ddd;
        cursor: not-allowed;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }
</style>

<?php
session_start();
include_once '../../../config/database.php';

if (isset($_GET['taskid'])) {
    $taskid = $_GET['taskid'];
    $taskQuery = "SELECT * FROM tasks WHERE taskid = ?";
    $stmt = $conn->prepare($taskQuery);
    $stmt->bind_param("i", $taskid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy công việc.";
        exit;
    }

    // Lấy danh sách trạng thái từ ENUM
    $statusQuery = "SHOW COLUMNS FROM tasks LIKE 'status'";
    $statusResult = $conn->query($statusQuery);
    $statuses = [];
    if ($statusResult && $statusResult->num_rows > 0) {
        $row = $statusResult->fetch_assoc();
        preg_match_all("/'([^']+)'/", $row['Type'], $matches);
        $statuses = $matches[1];
    }
    ?>

<h2>Chỉnh sửa công việc</h2>
<form id="editTaskForm">
    <input type="hidden" name="taskid" value="<?php echo $task['taskid']; ?>">

    <label for="created_at">Ngày tạo:</label>
    <input type="text" id="created_at" name="created_at" value="<?php echo htmlspecialchars($task['created_at']); ?>" readonly><br>

    <label for="title">Tiêu đề:</label>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br>

    <label for="description">Mô tả:</label>
    <textarea id="description" name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea><br>

    <label for="file">Tệp đính kèm:</label>
    <input type="file" id="file" name="file"><br>

    <label for="status">Trạng thái:</label>
    <select id="status" name="status" required>
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo $status; ?>" <?php echo $task['status'] === $status ? 'selected' : ''; ?>>
                    <?php echo ucfirst($status); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

    <label for="assigned_username">Người được giao:</label>
    <input type="text" id="assigned_username" name="assigned_username" value="<?php echo htmlspecialchars($task['assigned_username']); ?>" required><br>

    <label for="deadline">Hạn chót:</label>
    <input type="date" id="deadline" name="deadline" value="<?php echo htmlspecialchars($task['deadline']); ?>" required><br>

    <button type="submit">Cập nhật</button>
</form>

<?php
} else {
    echo "Không có TaskID để sửa.";
}
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById("editTaskForm").addEventListener("submit", function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '/admin/dashboard/tasks/update_task.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.trim() === "success") {
                    alert("Cập nhật thành công!");
                    $('#dashboard-content').load('/admin/dashboard/tasks/tasks.php');
                } else {
                    alert("Có lỗi xảy ra: " + response);
                }
            },
            error: function() {
                alert("Lỗi kết nối tới máy chủ!");
            }
        });
    });
</script>