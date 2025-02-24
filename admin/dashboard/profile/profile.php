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
LEFT JOIN positions ON employees.position = positions.id; "; 


// Truy vấn lấy thông tin người dùng từ bảng users
$getUserQuery = "SELECT users.*, 
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
LEFT JOIN positions ON employees.position = positions.id WHERE username = ?";
$stmt = $conn->prepare($getUserQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Kiểm tra nếu không tìm thấy người dùng
if (!$user) {
    echo "Không tìm thấy người dùng!";
    exit;
}

// Lấy user_id của người dùng từ kết quả
$user_id = $user['id'];

// Truy vấn lấy thông tin nhân viên từ bảng employees dựa trên user_id
$getEmployeeQuery = "SELECT users.*, 
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
LEFT JOIN positions ON employees.position = positions.id WHERE user_id = ?";
$stmt = $conn->prepare($getEmployeeQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$employeeResult = $stmt->get_result();

// Kiểm tra nếu có dữ liệu nhân viên
$employee = $employeeResult->fetch_assoc();
?>

<div class="container-profile">
    <h2>Thông Tin Người Dùng</h2>

    <?php if (!$employee): ?>
        <div class="alert">
            <p>Vui lòng nhập thông tin nhân viên của bạn.</p>
        <a href="javascript:void(0);" onclick="loadUpdateForm();" class="btn">Điền thông tin</a>

        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Thuộc tính</th>
                <th>Giá trị</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Tên người dùng</strong></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
            </tr>
            
            <tr>
                <td><strong>Vai trò</strong></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if ($employee): ?>
        <h3>Thông Tin Nhân Viên</h3>
        <table>
            <thead>
                <tr>
                    <th>Thuộc tính</th>
                    <th>Giá trị</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Họ và tên</strong></td>
                    <td><?php echo htmlspecialchars($employee['employee_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo htmlspecialchars($employee['employee_email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Số điện thoại</strong></td>
                    <td><?php echo htmlspecialchars($employee['phone_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Phòng ban</strong></td>
                    <td><?php echo htmlspecialchars($employee['department_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Chức vụ</strong></td>
                    <td><?php echo htmlspecialchars($employee['position_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Địa chỉ</strong></td>
                    <td><?php echo htmlspecialchars($employee['address']); ?></td>
                </tr>
                <tr>
                    <td><strong>Thành phố</strong></td>
                    <td><?php echo htmlspecialchars($employee['city']); ?></td>
                </tr>
                <tr>
                    <td><strong>Quốc gia</strong></td>
                    <td><?php echo htmlspecialchars($employee['country']); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
function loadUpdateForm() {
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
</script>
<?php
$stmt->close();
$conn->close();
?>

<style>

.container-profile {
    margin: 30px auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    max-width: 800px;
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
    padding: 12px;
    text-align: left;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

.alert {
    padding: 20px;
    background-color: #f44336;
    color: white;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 5px;
}

.alert a {
    color: #fff;
    text-decoration: none;
    background-color: #4CAF50;
    padding: 10px 20px;
    border-radius: 5px;
}

.alert a:hover {
    background-color: #45a049;
}

.btn {
    display: inline-block;
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
}

.btn:hover {
    background-color: #45a049;
}
</style>
