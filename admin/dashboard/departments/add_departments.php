<?php
session_start();
include_once '../../../config/database.php';

// Xử lý form thêm phòng ban
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_name = $_POST['department_name'];
    $description = $_POST['description'];

    if (empty($department_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đủ thông tin']);
    } else {
        // Chuẩn bị câu truy vấn thêm phòng ban vào cơ sở dữ liệu
        $insertQuery = "INSERT INTO departments (department_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            $stmt->bind_param("ss", $department_name, $description);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Thêm phòng ban thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm phòng ban']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn']);
        }
    }
    $conn->close();
    exit;
}
?>

<!-- HTML form cho thêm phòng ban -->
<form id="add-department-form" method="POST">
    <label for="department_name">Tên phòng ban:</label>
    <input type="text" name="department_name" id="department_name" required>

    <label for="description">Mô tả:</label>
    <input type="text" name="description" id="description" >

    <button type="submit">Thêm Phòng Ban</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#add-department-form').submit(function(e) {
    e.preventDefault(); // Ngừng gửi form mặc định

    const department_name = $('#department_name').val();
    const description = $('#description').val();

    $.ajax({
        url: 'dashboard/departments/add_departments.php',
        method: 'POST',
        data: {
            department_name: department_name,
            description: description
        },
        success: function(response) {
            const result = JSON.parse(response);
            alert(result.message);

            if (result.status === 'success') {
                // Sau khi thêm thành công, tải lại danh sách phòng ban
                loadDepartments(); // Hàm này sẽ gọi AJAX tải lại danh sách phòng ban
            }
        },
        error: function() {
            alert('Lỗi khi thêm phòng ban.');
        }
    });
});

// Hàm tải lại danh sách phòng ban vào phần #dashboard-content
function loadDepartments() {
    $.ajax({
        url: 'dashboard/departments/departments.php', // Tải lại danh sách phòng ban
        method: 'GET',
        success: function(response) {
            // Cập nhật phần #dashboard-content với nội dung danh sách phòng ban
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách phòng ban.');
        }
    });
}
</script>

<style>
/* Đặt kiểu cho các phần tử trong form */
form {
    background-color: #fff;
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Đặt kiểu cho các nhãn trong form */
form label {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

/* Đặt kiểu cho các input và select trong form */
form input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

/* Đặt kiểu cho button */
form button[type="submit"] {
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

/* Thay đổi màu sắc button khi hover */
form button[type="submit"]:hover {
    background-color: #45a049;
}

/* Đặt kiểu cho các thông báo lỗi hoặc thành công */
.alert {
    padding: 10px;
    margin-top: 20px;
    text-align: center;
    border-radius: 4px;
    font-size: 16px;
}

/* Kiểu cho thông báo thành công */
.alert.success {
    background-color: #4CAF50;
    color: white;
}

/* Kiểu cho thông báo lỗi */
.alert.error {
    background-color: #f44336;
    color: white;
}
</style>
