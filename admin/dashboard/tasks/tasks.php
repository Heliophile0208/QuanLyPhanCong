<?php
session_start();
include_once '../../../config/database.php';
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit; // Dừng script sau khi chuyển hướng
}

// Lấy username từ session
$username = $_SESSION['username'];

// Truy vấn để lấy user_id và role từ bảng users
$query_user = "SELECT id, role FROM users WHERE username = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    // Lấy dữ liệu user
    $user_row = $result_user->fetch_assoc();
    $user_id = $user_row['id'];
    $role = $user_row['role'];

    // Kiểm tra nếu role không phải admin thì chuyển hướng về login
    if ($role !== 'admin') {
        header("Location: /login.php");
        exit;
    }
} else {
    // Không tìm thấy user, chuyển hướng về login
    header("Location: /login.php");
    exit;
}



// Lấy danh sách người dùng
$usersQuery = "SELECT * FROM users"; 


// Lấy danh sách tasks
$tasksQuery = "SELECT * FROM tasks";

// Xử lý tìm kiếm 
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE assigned_username LIKE ? OR title LIKE ?";
    $tasksQuery .= $searchQuery;
    
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($tasksQuery);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $tasksResult = $stmt->get_result();
} else {
    $tasksResult = $conn->query($tasksQuery);
}

// Kiểm tra kết quả truy vấn
if ($usersResult === FALSE) {
    echo "Lỗi truy vấn: " . $conn->error . "<br>";
}

if (isset($_POST['delete'])) {
    if (!isset($_POST['TaskID']) || empty($_POST['TaskID'])) {
        echo "<script>alert('Bạn chưa chọn công việc để xóa.');</script>";
    } else {
        $TaskIDToDelete = $_POST['TaskID'];
        $deleteQuery = "DELETE FROM tasks WHERE taskid = ?";
        $stmt = $conn->prepare($deleteQuery);

        if ($stmt) {
            $stmt->bind_param("i", $TaskIDToDelete);
            if ($stmt->execute()) {
                echo "<script>alert('Xóa công việc thành công!'); window.location.href = '/dashboard/tasks/tasks.php';</script>";
                exit;
            } else {
                echo "<script>alert('Lỗi khi xóa: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị truy vấn xóa: " . $conn->error . "');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tasks</title>
    <style>
        input[type="text"], button {
            padding: 10px;
            margin: 10px;
        }
        button[type="submit"] {
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h2>Quản lý Công Việc</h2>
    
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm task..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>

    <button type="button" onclick="loadAddTaskForm();">Thêm công việc</button>
    <form method="post" id="delete-form" style="display: inline;">
            <button type="button" class="editButton" onclick="setEditTask();">Sửa công việc</button>
        <button type="submit" name="delete" onclick="return confirmDelete();">Xóa công việc</button>
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Mô tả</th>
                    <th>Tệp</th>
                    <th>Tình trạng</th>
                    <th>Phân công</th>
                     <th>Deadline</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody id="tasks-table">
                <?php
                if (isset($tasksResult) && $tasksResult->num_rows > 0) {
                    while ($row = $tasksResult->fetch_assoc()) {
echo "<tr>
    <td><input type='radio' name='TaskID' value='" . htmlspecialchars($row['taskid']) . "' required></td>
    <td>" . htmlspecialchars($row['taskid']) . "</td>
    <td>" . htmlspecialchars($row['title']) . "</td>
    <td>" . htmlspecialchars($row['description']) . "</td>
    <td>";

if (!empty($row['file'])) {
    $filePath = $row['file'];
    $relativePath = strstr($filePath, "uploads/"); // Lấy phần sau "uploads/"

    if ($relativePath !== false) {
        echo "<a href='/" . htmlspecialchars($relativePath) . "' target='_blank'>Tải về</a>";
    } else {
        echo "Lỗi đường dẫn";
    }
} else {
    echo "Không có";
}

echo "</td>
    <td>" . htmlspecialchars($row['status']) . "</td>
    <td>" . htmlspecialchars($row['assigned_username']) . "</td>
    <td>" . htmlspecialchars($row['deadline']) . "</td>
    <td>" . htmlspecialchars($row['created_at']) . "</td>
</tr>";

                    }
                } else {
                    echo "<tr><td colspan='10'>Không có công việc nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>


    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function confirmDelete() {
            const selectedRadio = document.querySelector('input[name="TaskID"]:checked');
            if (!selectedRadio) {
                alert("Bạn chưa chọn công việc để xóa.");
                return false;
            }
            return confirm('Bạn có chắc chắn muốn xóa công việc này?');
        }

        function setEditTask() {
            const selectedRadio = document.querySelector('input[name="TaskID"]:checked');
            if (selectedRadio) {
                const taskid = selectedRadio.value;
                $.ajax({
                    url: 'dashboard/tasks/edit_task.php',
                    method: 'GET',
                    data: { taskid: taskid },
                    success: function(response) {
                        $('#dashboard-content').html(response);
                    },
                    error: function() {
                        alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
                    }
                });
            } else {
                alert("Bạn chưa chọn task để sửa.");
            }
        }

        function loadAddTaskForm() {
            $.ajax({
                url: 'dashboard/tasks/add_tasks.php',
                method: 'GET',
                success: function(response) {
                    $('#dashboard-content').html(response);
                },
                error: function() {
                    alert("Có lỗi khi tải trang thêm công việc.");
                }
            });
        }
        function loadDeleteTaskForm() {
            $.ajax({
                url: 'dashboard/tasks/tasks.php',
                method: 'GET',
                success: function(response) {
                    $('#dashboard-content').html(response);
                },
                error: function() {
                    alert("Có lỗi khi tải trang thêm công việc.");
                }
            });
        }

        // AJAX tìm kiếm 
        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/tasks/tasks.php',
                method: 'POST',
                data: { submit_search: true, search: searchTerm },
                success: function(response) {
                    // Cập nhật bảng 
                    $('#tasks-table').html($(response).find('#tasks-table').html());
                }
            });
        });

        // AJAX xóa 
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="TaskID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn công việc để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa công việc này?')) {
                const TaskID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/tasks/tasks.php',
                    method: 'POST',
                    data: { delete: true, TaskID: TaskID },
                    success: function(response) {
                        alert('Xóa công việc thành công!');
                        loadDeleteTaskForm(); // Reload lại trang để cập nhật danh sách người dùng
                    }
                });
            }
        });
    </script>
</body>
</html>