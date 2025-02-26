<?php
session_start();
include_once '../../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.']);
    exit;
}

// Lấy danh sách phòng ban từ cơ sở dữ liệu
$query = "SELECT id, department_name FROM departments";
$result = $conn->query($query);

// Lưu danh sách phòng ban vào mảng
$departments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Xử lý thêm chức vụ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $position_name = $_POST['position_name'];
    $description = $_POST['description'];
    $department_id = $_POST['department_id'];

    // Kiểm tra dữ liệu đầu vào
    if (empty($position_name) || empty($department_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
    } else {
        // Chuẩn bị truy vấn SQL
        $insertQuery = "INSERT INTO positions (department_id, position_name, description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            $stmt->bind_param("iss", $department_id, $position_name, $description);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Thêm chức vụ thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm chức vụ.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi truy vấn SQL.']);
        }
    }
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Chức Vụ</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
/* Kiểu cho form */
form {
    background-color: #fff;
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

form label {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

form input[type="text"], form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

form button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
}

form button:hover {
    background-color: #45a049;
}

.alert {
    padding: 10px;
    margin-top: 20px;
    text-align: center;
    border-radius: 4px;
    font-size: 16px;
}

.alert.success {
    background-color: #4CAF50;
    color: white;
}

.alert.error {
    background-color: #f44336;
    color: white;
}
</style>

<body>
    <div class="container">
        <h2>Thêm Chức Vụ</h2>

        <form id="add-position-form">
            <label for="position_name">Tên Chức Vụ:</label>
            <input type="text" name="position_name" id="position_name" required>

            <label for="description">Mô Tả:</label>
            <input type="text" name="description" id="description">

            <label for="department_id">Phòng Ban:</label>
            <select name="department_id" id="department_id" required>
                <option value="">-- Chọn Phòng Ban --</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?php echo $department['id']; ?>">
                        <?php echo htmlspecialchars($department['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Thêm Chức Vụ</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>

<script>
$('#add-position-form').submit(function(e) {
    e.preventDefault(); // Ngừng gửi form mặc định

    const position_name = $('#position_name').val();
    const description = $('#description').val();
    const department_id = $('#department_id').val();

    $.ajax({
        url: 'dashboard/positions/add_positions.php',
        method: 'POST',
        data: {
            position_name: position_name,
            description: description,
            department_id: department_id
        },
        success: function(response) {
            const result = JSON.parse(response);
            alert(result.message);

            if (result.status === 'success') {
                // Sau khi thêm thành công, tải lại danh sách chức vụ
                loadPositions();
            }
        },
        error: function() {
            alert('Có lỗi khi thêm chức vụ.');
        }
    });
});

// Hàm tải lại danh sách chức vụ vào phần #dashboard-content
function loadPositions() {
    $.ajax({
        url: 'dashboard/positions/positions.php', // Tải lại danh sách chức vụ
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách chức vụ.');
        }
    });
}
</script>
