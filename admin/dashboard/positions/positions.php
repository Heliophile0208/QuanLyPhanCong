<?php
session_start();
include_once '../../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: /login.php");
    exit;
}

// Lấy thông tin người dùng
$username = $_SESSION['username'];
$query_user = "SELECT id, role FROM users WHERE username = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user_row = $result_user->fetch_assoc();
    $user_id = $user_row['id'];
    $role = $user_row['role'];

    if ($role !== 'admin') {
        header("Location: /login.php");
        exit;
    }
} else {
    header("Location: /login.php");
    exit;
}

// Lấy danh sách phòng ban
$departmentsQuery = "SELECT * FROM departments";
$departmentsResult = $conn->query($departmentsQuery);

// Lấy danh sách chức vụ
$positionsQuery = "SELECT positions.*, departments.department_name FROM positions 
                   LEFT JOIN departments ON positions.department_id = departments.id";

if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $positionsQuery .= " WHERE positions.position_name LIKE ?";
    $stmt = $conn->prepare($positionsQuery);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $positionsResult = $stmt->get_result();
} else {
    $positionsResult = $conn->query($positionsQuery);
}

// Xử lý xóa chức vụ
if (isset($_POST['delete'])) {
    if (!empty($_POST['PositionID'])) {
        $PositionID = $_POST['PositionID'];
        $deleteQuery = "DELETE FROM positions WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $PositionID);
        if ($stmt->execute()) {
            echo "<script>alert('Xóa chức vụ thành công!'); window.location.href = '/dashboard/positions.php';</script>";
            exit;
        } else {
            echo "<script>alert('Lỗi khi xóa chức vụ: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Bạn chưa chọn chức vụ để xóa.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chức vụ</title>
    <style>
        input[type="text"], button {
            padding: 10px;
            margin: 10px;
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
    <h2>Quản lý Chức vụ</h2>

    <!-- Form Tìm kiếm Chức vụ -->
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm chức vụ..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>

    <!-- Form Thêm Chức vụ -->
    <button type="button" onclick="loadAddPositionForm();">Thêm Chức vụ</button>
    
    <form method="post" id="delete-form" style="display: inline;">
      <button type="button" class="editButton" onclick="setEditPosition();">Sửa Chức vụ</button>
        <button type="submit" name="delete" onclick="return confirmDelete();">Xóa Chức vụ</button>
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>ID</th>
                    <th>Tên Chức vụ</th>
                    <th>Mô tả</th>
                    <th>Phòng ban</th>
                </tr>
            </thead>
            <tbody id="positions-table">
                <?php
                if (isset($positionsResult) && $positionsResult->num_rows > 0) {
                    while ($row = $positionsResult->fetch_assoc()) {
                        echo "<tr>
                            <td><input type='radio' name='PositionID' value='" . htmlspecialchars($row['id']) . "' required></td>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['position_name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>" . htmlspecialchars($row['department_name']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Không có chức vụ nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

      
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function setEditPosition() {
            const selectedRadio = document.querySelector('input[name="PositionID"]:checked');
            if (selectedRadio) {
                const PositionID = selectedRadio.value;
                $.ajax({
                    url: 'dashboard/positions/edit_position.php',
                    method: 'GET',
                    data: { PositionID: PositionID },
                    success: function(response) {
                        $('#dashboard-content').html(response);
                    },
                    error: function() {
                        alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
                    }
                });
            } else {
                alert("Bạn chưa chọn chức vụ để sửa.");
            }
        }

        function confirmDelete() {
            const selectedRadio = document.querySelector('input[name="PositionID"]:checked');
            if (!selectedRadio) {
                alert("Bạn chưa chọn chức vụ để xóa.");
                return false;
            }
            return confirm('Bạn có chắc chắn muốn xóa chức vụ này?');
        }
  // AJAX xóa 
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="PositionID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn chức vụ để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa chức vụ này?')) {
                const PositionID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/positions/positions.php',
                    method: 'POST',
                    data: { delete: true, PositionID: PositionID },
                    success: function(response) {
                        alert('Xóa chức vụ thành công!');
                        loadPosition(); // Reload lại trang để cập nhật danh sách 
                    }
                });
            }
        });
        function loadAddPositionForm() {
            $.ajax({
                url: 'dashboard/positions/add_positions.php',
                method: 'GET',
                success: function(response) {
                    $('#dashboard-content').html(response);
                },
                error: function() {
                    alert("Có lỗi khi tải trang thêm chức vụ.");
                }
            });
        }

        function loadPosition() {
            $.ajax({
                url: 'dashboard/positions/positions.php',  
                method: 'POST',
                success: function(response) {
                    $('#positions-table').html($(response).find('#positions-table').html());
                },
                error: function() {
                    alert('Có lỗi khi tải lại danh sách chức vụ.');
                }
            });
        }
    </script>
</body>
</html>
