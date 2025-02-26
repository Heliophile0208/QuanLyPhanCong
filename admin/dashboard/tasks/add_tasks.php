<?php
session_start();
include_once '../../../config/database.php';

// Kiểm tra kết nối cơ sở dữ liệu
if (!$conn) {
    die(json_encode(['status' => 'error', 'message' => 'Lỗi kết nối CSDL: ' . $conn->connect_error]));
}

// Xử lý khi có dữ liệu gửi lên bằng phương thức POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $title = isset($_POST['title']) ? trim($_POST['title']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $status = isset($_POST['status']) ? trim($_POST['status']) : null;
    $assigned_username = isset($_POST['assigned_user']) ? trim($_POST['assigned_user']) : null;
    $deadline = isset($_POST['deadline']) ? $_POST['deadline'] : null;
    $file = isset($_FILES['file']) ? $_FILES['file'] : null;

    // Kiểm tra dữ liệu nhập vào
    if (empty($title) || empty($description) || empty($status) || empty($assigned_username) || empty($deadline)) {
        die(json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin.']));
    }

    // Kiểm tra trạng thái hợp lệ
    $valid_statuses = ['Chưa hoàn thành', 'Đang gặp vấn đề', 'Đã hoàn thành'];
    if (!in_array($status, $valid_statuses)) {
        die(json_encode(['status' => 'error', 'message' => 'Trạng thái không hợp lệ: ' . $status]));
    }

// Xử lý file upload (nếu có)
$filePath = NULL;
if (!empty($file) && $file['size'] > 0) {
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";  // Đường dẫn tuyệt đối
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Tạo thư mục nếu chưa có
    }

    $fileName = time() . '_' . basename($file['name']); // Tránh trùng tên file
    $filePath = $targetDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        die(json_encode(['status' => 'error', 'message' => 'Lỗi khi tải lên tệp.']));
    }
}


    // Chuẩn bị câu lệnh SQL
    $insertQuery = "INSERT INTO tasks (title, description, file, status, assigned_username, deadline, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($insertQuery);
    if (!$stmt) {
        die(json_encode(['status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]));
    }

    // Gán dữ liệu vào câu lệnh SQL
    $stmt->bind_param("ssssss", $title, $description, $filePath, $status, $assigned_username, $deadline);

    // Thực thi câu lệnh SQL
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Thêm task thành công!']);
    } else {
        die(json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm task: ' . $stmt->error]));
    }

    // Đóng kết nối
    $stmt->close();
    $conn->close();
    exit;
}
?>
<p id="upload-status" style="font-weight: bold;"></p>
<h2>Phân công</h2>
<form id="add-task-form" method="POST" enctype="multipart/form-data">
    <div class="form-container">
        <!-- Cột 1 -->
        <div class="form-column">
            <label for="title">Tiêu đề:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Mô tả:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="file">Tệp đính kèm:</label>
            <input type="file" name="file" id="file">
        </div>

        <!-- Cột 2 -->
        <div class="form-column">
            <label for="status">Trạng thái:</label>
            <select name="status" id="status" required>
                <option value="Chưa hoàn thành">Chưa hoàn thành</option>
                <option value="Đang gặp vấn đề">Đang gặp vấn đề</option>
                <option value="Đã hoàn thành">Đã hoàn thành</option>
            </select>

            <label for="deadline">Hạn chót:</label>
            <input type="date" name="deadline" id="deadline" required>

            <label for="assigned_user">Người được giao:</label>
            <select name="assigned_user" id="assigned_user" required>
                <?php
                include_once '../../../config/database.php';
                $userQuery = "SELECT username FROM users";
                $users = $conn->query($userQuery);
                while ($row = $users->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['username']) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>

    <button type="submit">Thêm Task</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#add-task-form').submit(function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    // Hiển thị trạng thái tải lên
    $('#upload-status').text('Đang tải lên...').css('color', 'blue');

    $.ajax({
        url: 'dashboard/tasks/add_tasks.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            const result = JSON.parse(response);
            $('#upload-status').text(result.message).css('color', result.status === 'success' ? 'green' : 'red');
            
            if (result.status === 'success') {
                loadTasks();
            }
        },
        error: function() {
            $('#upload-status').text('Có lỗi xảy ra khi tải lên!').css('color', 'red');
        }
    });
});

function loadTasks() {
    $.ajax({
        url: 'dashboard/tasks/tasks.php',
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách tasks.');
        }
    });
}
</script>
<style>

    .form-container {
        display: flex;
        gap: 20px;
        width: 90%;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin: 50px auto;
    }

    .form-column {
        flex: 1;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    input[type="text"],
    input[type="date"],
    textarea,
    select,
    input[type="file"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        box-sizing: border-box;
    }

    textarea {
        height: 80px;
        resize: vertical;
    }

    select {
        cursor: pointer;
    }

    button {
        display: block;
        width: 90%;
        padding: 12px;
        background-color: #4CAF50;
        color: white;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin:0 auto;
        transition: background 0.3s ease;
    }

    button:hover {
        background-color: #45a049;
    }

    button:disabled {
        background-color: #ccc;
        cursor: not-allowed;
    }

    @media (max-width: 600px) {
        .form-container {
            flex-direction: column;
        }
    }
</style>
