<?php
session_start();
include_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $positionID = $_POST['positionID'];
    $position_name = $_POST['position_name'];
    $description = $_POST['description'];
    $department_id = $_POST['department_id'];

    // Kiểm tra xem positionID có tồn tại trong cơ sở dữ liệu
    $checkQuery = "SELECT * FROM positions WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $positionID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu có thay đổi, cập nhật thông tin chức vụ
        $updateQuery = "UPDATE positions SET position_name = ?, description = ?, department_id = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssii", $position_name, $description, $department_id, $positionID);

        if ($stmt->execute()) {
            // Trả về thông báo thành công dưới dạng text
            echo "success";
        } else {
            echo "Lỗi khi cập nhật: " . $conn->error;
        }
    } else {
        echo "Chức vụ không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>