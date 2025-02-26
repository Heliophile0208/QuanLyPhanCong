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
$usersQuery = "SELECT users.*, 
       employees.employee_name, 
       employees.employee_email, 
       employees.phone_number, 
       employees.address, 
       employees.city, 
       employees.state, 
       employees.postal_code, 
       employees.country, 
       employees.department_id, 
       departments.department_name, 
       employees.position, 
       positions.position_name
FROM users 
LEFT JOIN employees ON users.id = employees.user_id
LEFT JOIN departments ON employees.department_id = departments.id
LEFT JOIN positions ON employees.position = positions.id; -- Sửa JOIN đúng cột ID của position
";


// Xử lý tìm kiếm người dùng với Prepared Statement
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE username LIKE ? OR role LIKE ?";
    $usersQuery .= $searchQuery;
    
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($usersQuery);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $usersResult = $stmt->get_result();
} else {
    $usersResult = $conn->query($usersQuery);
}

// Kiểm tra kết quả truy vấn
if ($usersResult === FALSE) {
    echo "Lỗi truy vấn: " . $conn->error . "<br>";
}

if (isset($_POST['delete'])) {
    if (isset($_POST['UserID']) && !empty($_POST['UserID'])) {
        $UserIDToDelete = $_POST['UserID'];
         // Truy vấn lấy username trước khi xóa
        $queryUsername = "SELECT username FROM users WHERE id = ?";
        $stmtUsername = $conn->prepare($queryUsername);
        $stmtUsername->bind_param("i", $UserIDToDelete);
        $stmtUsername->execute();
        $resultUsername = $stmtUsername->get_result();
        if ($resultUsername->num_rows > 0) {
            $row = $resultUsername->fetch_assoc();
            $usernameToDelete = $row['username'];
        } else {
            echo "<script>alert('Không tìm thấy người dùng.');</script>";
            exit;
        }
        $stmtUsername->close();

        // Xóa tất cả nhiệm vụ (tasks) có assigned_username trùng với username của user bị xóa
        $deleteTasksQuery = "DELETE FROM tasks WHERE assigned_username = ?";
        $stmtTasks = $conn->prepare($deleteTasksQuery);
        $stmtTasks->bind_param("s", $usernameToDelete);
        $stmtTasks->execute();
        $stmtTasks->close();
        // Xóa tất cả nhân viên liên quan trước
        $deleteEmployeesQuery = "DELETE FROM employees WHERE user_id = ?";
        $stmtEmployees = $conn->prepare($deleteEmployeesQuery);
        $stmtEmployees->bind_param("i", $UserIDToDelete);
        $stmtEmployees->execute();
        $stmtEmployees->close();

        // Sau đó xóa user
        $deleteQuery = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        if ($stmt) {
            $stmt->bind_param("i", $UserIDToDelete);
            if ($stmt->execute()) {
                echo "<script>alert('Xóa người dùng thành công!'); window.location.href = '/dashboard/users.php';</script>";
                exit;
            } else {
                echo "<script>alert('Lỗi khi xóa người dùng: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị truy vấn xóa người dùng.');</script>";
        }
    } else {
        echo "<script>alert('Bạn chưa chọn người dùng để xóa.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng</title>
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
        .table-container {
    width: 100%;
    overflow-x: auto; /* Tạo thanh cuộn ngang */
    white-space: nowrap; /* Ngăn nội dung xuống dòng */
}
.tooltip {
    position: relative;
    cursor: pointer;
    display: inline-block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 150px;
}

.tooltip:hover::after {
    content: attr(title);
    position: absolute;
    background-color: #333;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: normal;
    max-width: 300px;
    left: 50%;
    top: 100%;
    transform: translateX(-50%) translateY(5px);
    z-index: 999;
    font-size: 12px;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
    word-wrap: break-word;
}


    </style>
</head>
<body>
    <h2>Quản lý Người dùng</h2>

    <!-- Form Tìm kiếm User -->
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm người dùng..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>


    <!-- Form Thêm User -->
  <button type="button" onclick="loadAddUserForm();">Thêm</button>
    <form method="post" id="delete-form" style="display: inline;">
       <!-- Nút sửa user -->
        <button type="button" class="editButton" onclick="setEditUser();">Sửa</button>
        <button type="submit" name="delete" onclick="return confirmDelete();">Xóa</button>
   <button type="button" onclick="loadUpdateEmployeeForm();">Cập Nhật Thông Tin</button>
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Chọn</th>
                <th>Username</th>
                <th>Vị Trí</th>
                <th>Vai Trò</th>
                <th>Phòng Ban</th>
                <th>Tên Nhân Viên</th>
                <th>Email</th>
                <th>Số Điện Thoại</th>
                
                <th>Địa Chỉ</th>
                
            </tr>
        </thead>
        <tbody id="users-table">
            <?php
            if ($usersResult->num_rows > 0) {
                while ($row = $usersResult->fetch_assoc()) {
                    echo "<tr>
                        <td><input type='radio' name='UserID' value='" . htmlspecialchars($row['id']) . "' required></td>
                        <td>" . htmlspecialchars($row['username']) . "</td>
                        <td>" . (!empty($row['position_name']) ? htmlspecialchars($row['position_name']) : "<span style='color:red;'>Chưa có</span>") . "</td>
                        <td>" . htmlspecialchars($row['role']) . "</td>
                         <td>" . (!empty($row['department_id']) ? htmlspecialchars($row['department_name']) : "<span style='color:red;'>Chưa có</span>") . "</td>
                        <td>" . (!empty($row['employee_name']) ? htmlspecialchars($row['employee_name']) : "<span style='color:red;'>Chưa có</span>") . "</td>
                         <td><span class='tooltip' title='" . htmlspecialchars($row['employee_email']) . "'>
        " . (strlen($row['employee_email']) > 10 ? htmlspecialchars(substr($row['employee_email'], 0, 10)) . '...' : htmlspecialchars($row['employee_email'])) . " </span></td>
                        <td>" . (!empty($row['phone_number']) ? htmlspecialchars($row['phone_number']) : "<span style='color:red;'>Chưa có</span>") . "</td>
                        
                        <td><span class='tooltip' title='" . htmlspecialchars($row['address']) . "'>
        " . (strlen($row['address']) > 20 ? htmlspecialchars(substr($row['address'], 0, 20)) . '...' : htmlspecialchars($row['address'])) . " </span></td>
                       
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='11'>Không có người dùng nào.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

function loadUpdateEmployeeForm() {
    $.ajax({
        url: 'dashboard/profile/update_employee.php',
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải form cập nhật.');
        }
    });
}

  // AJAX chỉnh sửa người dùng
    function setEditUser() {
        const selectedRadio = document.querySelector('input[name="UserID"]:checked');
        if (selectedRadio) {
            const UserID = selectedRadio.value;
            $.ajax({
                url: 'dashboard/users/edit_user.php',  // Tệp sẽ xử lý chỉnh sửa
                method: 'GET',
                data: { UserID: UserID },
                success: function(response) {
                    // Hiển thị nội dung chỉnh sửa trong phần content
                    $('#dashboard-content').html(response); 
                },
                error: function() {
                    alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
                }
            });
        } else {
            alert("Bạn chưa chọn người dùng để sửa.");
        }
    }

        function confirmDelete() {
            const selectedRadio = document.querySelector('input[name="UserID"]:checked');
            if (!selectedRadio) {
                alert("Bạn chưa chọn người dùng để xóa.");
                return false; // Ngăn không cho form gửi đi
            }
            return confirm('Bạn có chắc chắn muốn xóa người dùng này?');
        }

        // AJAX tìm kiếm người dùng
        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/users/users.php',
                method: 'POST',
                data: { submit_search: true, search: searchTerm },
                success: function(response) {
                    // Cập nhật bảng người dùng
                    $('#users-table').html($(response).find('#users-table').html());
                }
            });
        });

        // AJAX xóa người dùng
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="UserID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn người dùng để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
                const UserID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/users/users.php',
                    method: 'POST',
                    data: { delete: true, UserID: UserID },
                    success: function(response) {
                        alert('Xóa người dùng thành công!');
                        loadUsers(); // Reload lại trang để cập nhật danh sách người dùng
                    }
                });
            }
        });

// Hàm gửi UserID qua AJAX và cập nhật nội dung trong dashboard-content
// Hàm tải trang thêm người dùng vào phần dashboard-content
function loadAddUserForm() {
    $.ajax({
        url: 'dashboard/users/add_user.php',  // Truyền tới trang thêm người dùng
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);  // Hiển thị form thêm người dùng
        },
        error: function() {
            alert("Có lỗi khi tải trang thêm người dùng.");
        }
    });
}

// Sau khi thêm người dùng thành công
function handleAddUserSuccess() {
    alert('Thêm người dùng thành công!');
    loadUsers();  // Cập nhật lại danh sách người dùng
}
    // Hàm tải lại danh sách người dùng sau khi thêm mới
    function loadUsers() {
        $.ajax({
            url: 'dashboard/users/users.php',  // Truyền đến trang xử lý lấy lại danh sách người dùng
            method: 'POST',
            success: function(response) {
                $('#users-table').html($(response).find('#users-table').html());
            },
            error: function() {
                alert('Có lỗi khi tải lại danh sách người dùng.');
            }
        });
    }
</script>
    
</body>
</html>