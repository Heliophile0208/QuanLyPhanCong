<?php
include '../config/database.php';

if(isset($_POST['task_id']) && isset($_POST['status'])) {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    $sql = "UPDATE tasks SET status = '$status' WHERE taskid = '$task_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: tasks.php");
        exit();
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>
