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

// Lấy danh sách phòng ban
$departmentsQuery = "SELECT * FROM departments";

// Xử lý tìm kiếm phòng ban với Prepared Statement
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE department_name LIKE ? OR description LIKE ?";
    $departmentsQuery .= $searchQuery;
    
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($departmentsQuery);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $departmentsResult = $stmt->get_result();
} else {
    $departmentsResult = $conn->query($departmentsQuery);
}

// Kiểm tra kết quả truy vấn
if ($departmentsResult === FALSE) {
    echo "Lỗi truy vấn: " . $conn->error . "<br>";
}

// Xử lý xóa phòng ban và người dùng liên quan
if (isset($_POST['delete'])) {
    if (isset($_POST['DepartmentID']) && !empty($_POST['DepartmentID'])) {
        $DepartmentIDToDelete = $_POST['DepartmentID'];

        // Xóa người dùng thuộc phòng ban này
        $deleteUsersQuery = "DELETE FROM employees WHERE department_id = ?";
        $stmt = $conn->prepare($deleteUsersQuery);
        if ($stmt) {
            $stmt->bind_param("i", $DepartmentIDToDelete);
            $stmt->execute();
            $stmt->close();
        }

        // Xóa phòng ban
        $deleteDepartmentQuery = "DELETE FROM departments WHERE id = ?";
        $stmt = $conn->prepare($deleteDepartmentQuery);
        if ($stmt) {
            $stmt->bind_param("i", $DepartmentIDToDelete);
            if ($stmt->execute()) {
                echo "<script>alert('Xóa phòng ban và người dùng thành công!'); window.location.href = '/dashboard/departments.php';</script>";
                exit; // Dừng script sau khi chuyển hướng
            } else {
                echo "<script>alert('Lỗi khi xóa phòng ban: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị truy vấn xóa phòng ban.');</script>";
        }
    } else {
        echo "<script>alert('Bạn chưa chọn phòng ban để xóa.');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Phòng Ban</title>
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
    <h2>Quản lý Phòng Ban</h2>

    <!-- Form Tìm kiếm -->
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm phòng ban...">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>

    <button type="button" onclick="loadAddDepartmentForm();">Thêm Phòng Ban</button>
    <form method="post" id="delete-form" style="display: inline;">
    
        <button type="button" class="editButton" onclick="setEditDepartment();">Sửa Phòng Ban</button>
      <button type="submit" onclick="return confirmDeleteDepartment();">Xóa Phòng Ban</button>

        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>ID</th>
                    <th>Tên Phòng Ban</th>
                    <th>Mô Tả</th>
                </tr>
            </thead>
            <tbody id="departments-table">
                <?php
                if (isset($departmentsResult) && $departmentsResult->num_rows > 0) {
                    while ($row = $departmentsResult->fetch_assoc()) {
                        echo "<tr>
                            <td><input type='radio' name='DepartmentID' value='" . htmlspecialchars($row['id']) . "' required></td>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['department_name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Không Có Phòng Ban nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

function loadAddDepartmentForm() {
    $.ajax({
        url: 'dashboard/departments/add_departments.php',  // Truyền tới trang thêm
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);  // Hiển thị form thêm 
        },
        error: function() {
            alert("Có lỗi khi tải trang thêm phòng ban.");
        }
    });
}
    // Hàm gửi DepartmentID qua AJAX để sửa
    function setEditDepartment() {
        const selectedRadio = document.querySelector("input[name='DepartmentID']:checked");
        if (selectedRadio) {
            const DepartmentID = selectedRadio.value;
            $.ajax({
                url: "dashboard/departments/edit_departments.php",
                method: "GET",
                data: { DepartmentID: DepartmentID },
                success: function (response) {
                    $("#dashboard-content").html(response);
                },
                error: function () {
                    alert("Có lỗi khi tải dữ liệu chỉnh sửa phòng ban.");
                }
            });
        } else {
            alert("Bạn chưa chọn phòng ban để sửa.");
        }
    }

    // Hàm xác nhận xóa phòng ban
    function confirmDeleteDepartment() {
        const selectedRadio = document.querySelector("input[name='DepartmentID']:checked");
        if (!selectedRadio) {
            alert("Bạn chưa chọn phòng ban để xóa.");
            return false;
        }
        return confirm("Bạn có chắc chắn muốn xóa phòng ban này?");
    }

        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/departments/departments.php',
                method: 'POST',
                data: { submit_search: true, search: searchTerm },
                success: function(response) {
                     $('#departments-table').html($(response).find('#departments-table').html());
                }
            });
        });
       // Xóa phòng ban bằng AJAX
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="DepartmentID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn phòng ban để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa phòng ban này?')) {
                const DepartmentID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/departments/departments.php',
                    method: 'POST',
                    data: { delete: true, DepartmentID: DepartmentID },
                    success: function(response) {
                        alert('Xóa phòng ban thành công!');
                        loadDepartments(); // Reload lại trang để cập nhật danh sách người dùng
                    }
                });
            }
        });
    // Sau khi thêm phòng ban thành công
    function handleAddDepartmentSuccess() {
        alert("Thêm phòng ban thành công!");
        loadDepartments();
    }

    // Hàm tải lại danh sách phòng ban
    function loadDepartments() {
        $.ajax({
            url: "dashboard/departments/departments.php",
            method: "POST",
            success: function (response) {
                $("#departments-table").html($(response).find("#departments-table").html());
            },
            error: function () {
                alert("Có lỗi khi tải lại danh sách phòng ban.");
            }
        });
    }
    </script>
</body>
</html>
