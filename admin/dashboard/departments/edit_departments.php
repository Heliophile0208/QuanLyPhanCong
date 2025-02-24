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

if (isset($_GET['DepartmentID'])) {
    $DepartmentID = $_GET['DepartmentID'];
    $departmentQuery = "SELECT * FROM departments WHERE id = ?";
    $stmt = $conn->prepare($departmentQuery);
    $stmt->bind_param("i", $DepartmentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $department = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy phòng ban.";
        exit;
    }

    // Lấy danh sách phòng ban nếu cần
    $departmentQuery = "SELECT id, department_name FROM departments";
    $departmentResult = $conn->query($departmentQuery);
    $departments = [];
    if ($departmentResult && $departmentResult->num_rows > 0) {
        while ($row = $departmentResult->fetch_assoc()) {
            $departments[] = $row;
        }
    }
}
?>
<h2>Chỉnh sửa phòng ban</h2>
<form id="editDepartmentForm">
    <input type="hidden" name="DepartmentID" value="<?php echo $department['id']; ?>">
    
    <label for="department_name">Tên phòng ban:</label>
    <input type="text" id="department_name" name="department_name" value="<?php echo htmlspecialchars($department['department_name']); ?>" required><br>

    <label for="description">Mô tả:</label>
    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($department['description']); ?>"><br>

    <button type="submit">Cập nhật</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Lắng nghe sự kiện submit form
    document.getElementById("editDepartmentForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Ngăn trang web tải lại

        // Lấy dữ liệu từ form
        var formData = new FormData(this);

        $.ajax({
            url: '/admin/dashboard/departments/update_departments.php', // URL xử lý cập nhật
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.trim() === "success") {
                    alert("Cập nhật thành công!");
                    // Bạn có thể cập nhật lại bảng users hoặc làm gì đó sau khi thành công
                    $('#dashboard-content').load('/admin/dashboard/departments/departments.php'); // Ví dụ tải lại trang users.php trong phần content
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
