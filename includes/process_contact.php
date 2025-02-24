<?php
session_start();
include_once '../config/database.php'; // Kết nối database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Kiểm tra dữ liệu có rỗng không
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['contact_error'] = "Vui lòng điền đầy đủ thông tin!";
        header("Location: contact.php");
        exit();
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Email không hợp lệ!";
        header("Location: contact.php");
        exit();
    }

    // Chuẩn bị câu lệnh SQL
    $sql = "INSERT INTO contact (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        $_SESSION['contact_success'] = "Gửi tin nhắn thành công! Chúng tôi sẽ phản hồi sớm nhất.";
    } else {
        $_SESSION['contact_error'] = "Đã xảy ra lỗi khi gửi tin nhắn.";
    }

    $stmt->close();
    $conn->close();

    header("Location: contact.php");
    exit();
}
