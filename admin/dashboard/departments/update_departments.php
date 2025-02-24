<?php
session_start();
include_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $DepartmentID = $_POST['DepartmentID'];
    $department_name = $_POST['department_name'];
    $description = $_POST['description'];

    // Kiểm tra xem DepartmentID có tồn tại trong cơ sở dữ liệu
    $checkQuery = "SELECT * FROM departments WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $DepartmentID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật thông tin phòng ban
        $updateQuery = "UPDATE departments SET department_name = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $department_name, $description, $DepartmentID);

        if ($stmt->execute()) {
            // Trả về thông báo thành công dưới dạng text
            echo "success";
        } else {
            echo "Lỗi khi cập nhật: " . $conn->error;
        }
    } else {
        echo "Phòng ban không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>
