<?php
include_once '../../../config/database.php';

if (isset($_GET['department_id'])) {
    $department_id = intval($_GET['department_id']);

    $query = "SELECT id, position_name FROM positions WHERE department_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $positions = [];
    while ($row = $result->fetch_assoc()) {
        $positions[] = $row;
    }

    echo json_encode($positions);
}

$stmt->close();
$conn->close();
?>
