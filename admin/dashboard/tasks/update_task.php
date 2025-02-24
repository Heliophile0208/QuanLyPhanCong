<?php
session_start();
include_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskid = $_POST['taskid'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $assigned_username = $_POST['assigned_username'];
    $deadline = $_POST['deadline'];

    // Kiểm tra xem task có tồn tại trong cơ sở dữ liệu
    $checkQuery = "SELECT * FROM tasks WHERE taskid = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $taskid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật task
        $updateQuery = "UPDATE tasks SET title = ?, description = ?, status = ?, assigned_username = ?, deadline = ? WHERE taskid = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssssi", $title, $description, $status, $assigned_username, $deadline, $taskid);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Lỗi khi cập nhật: " . $conn->error;
        }
    } else {
        echo "Công việc không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>
