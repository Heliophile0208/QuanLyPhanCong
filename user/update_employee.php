<?php
session_start();
include_once '../config/database.php'; // Kết nối database
include_once '../includes/header.php'; // Kết nối database
// Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    die("Bạn chưa đăng nhập!");
}

$username = $_SESSION['username'];

// Lấy user_id từ bảng users
$userQuery = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$stmt->close();

if (!$userData) {
    die("Không tìm thấy thông tin người dùng.");
}

$user_id = $userData['id']; 
// Truy vấn thông tin nhân viên (bao gồm chức vụ và phòng ban)
$employeeQuery = "
SELECT e.employee_name, e.employee_email, e.phone_number, e.address, 
       e.city, e.state, e.postal_code, e.country, 
       p.position_name, d.department_name
FROM employees e
LEFT JOIN departments d ON e.department_id = d.id
LEFT JOIN positions p ON e.position = p.id
WHERE e.user_id = ?;
";

$stmt = $conn->prepare($employeeQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employeeData = $result->fetch_assoc(); // Lưu kết quả vào biến này
$stmt->close();

// Kiểm tra nếu form được gửi bằng phương thức POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_name = $_POST['employee_name'];
    $employee_email = $_POST['employee_email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];

    // Kiểm tra user_id đã tồn tại trong bảng employees chưa
    $checkQuery = "SELECT id FROM employees WHERE user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $numRows = $stmt->num_rows;
    $stmt->close();

    if ($numRows > 0) {
        // Nếu user_id đã tồn tại, thực hiện UPDATE
        $updateQuery = "UPDATE employees 
                        SET employee_name=?, employee_email=?, phone_number=?, address=?, city=?, state=?, postal_code=?, country=? 
                        WHERE user_id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssssssi", $employee_name, $employee_email, $phone_number, $address, $city, $state, $postal_code, $country, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Nếu user_id chưa tồn tại, thực hiện INSERT
        $insertQuery = "INSERT INTO employees (user_id, employee_name, employee_email, phone_number, address, city, state, postal_code, country) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("issssssss", $user_id, $employee_name, $employee_email, $phone_number, $address, $city, $state, $postal_code, $country);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();
    
    // Chuyển hướng sau khi lưu thành công
    header("Location: profile.php");
    exit;
}
?>
<style>
/* Định dạng chung cho form */
#add-employee-form {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

/* Bố cục 2 cột */
.form-container {
    display: flex;
    justify-content: center; /* Căn giữa 2 cột */
    gap: 20px;
}

.form-column {
    width: 50%;
    margin: 0 auto; /* Căn giữa */
    text-align: left; /* Giữ văn bản căn trái */
}

/* Nhãn (label) */
label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

/* Ô input */
input {
    width: 90%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
    transition: all 0.3s ease-in-out;
}

/* Khi focus vào input */
input:focus {
    border-color: #007bff;
    box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
    outline: none;
}

/* Định dạng nút */
.button-group {
    text-align: center;
    margin-top: 20px;
}

/* Nút submit */
button {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}

button:hover {
    background: #0056b3;
}

/* Nút quay lại */
.btn-back {
    display: inline-block;
    margin-left: 10px;
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    transition: 0.3s;
}

.btn-back:hover {
    background: #545b62;
}
h2 {margin:40px 0px 10px 0px;
text-align:center;}
</style>

<form id="add-employee-form" action="update_employee.php" method="POST">
    <h2>Cật nhật thông tin</h2>
    <div class="form-container">

        <div class="form-column">
            <label for="employee_name">Tên nhân viên:</label>
            <input type="text" name="employee_name" id="employee_name" value="<?= htmlspecialchars($employeeData['employee_name'] ?? '') ?>" required>

            <label for="employee_email">Email:</label>
            <input type="email" name="employee_email" id="employee_email" value="<?= htmlspecialchars($employeeData['employee_email'] ?? '') ?>" required>

            <label for="phone_number">Số điện thoại:</label>
            <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($employeeData['phone_number'] ?? '') ?>" required>

            <label for="position">Chức vụ:</label>
            <input type="text" name="position" id="position" value="<?= htmlspecialchars($employeeData['position_name'] ?? 'Chưa xác định') ?>" readonly>

            <label for="department_name">Phòng ban:</label>
            <input type="text" name="department_name" id="department_name" value="<?= htmlspecialchars($employeeData['department_name'] ?? 'Chưa xác định') ?>" readonly>
        </div>

        <div class="form-column">
            <label for="address">Địa chỉ:</label>
            <input type="text" name="address" id="address" value="<?= htmlspecialchars($employeeData['address'] ?? '') ?>">

            <label for="city">Thành phố:</label>
            <input type="text" name="city" id="city" value="<?= htmlspecialchars($employeeData['city'] ?? '') ?>">

            <label for="state">Tỉnh/Bang:</label>
            <input type="text" name="state" id="state" value="<?= htmlspecialchars($employeeData['state'] ?? '') ?>">

            <label for="postal_code">Mã bưu điện:</label>
            <input type="text" name="postal_code" id="postal_code" value="<?= htmlspecialchars($employeeData['postal_code'] ?? '') ?>">

            <label for="country">Quốc gia:</label>
            <input type="text" name="country" id="country" value="<?= htmlspecialchars($employeeData['country'] ?? '') ?>">
        </div>
    </div>
    <div class="button-group">
        <button type="submit">Lưu Nhân Viên</button>
        <a href="javascript:void(0);" class="btn btn-back" onclick="window.history.back();">Quay lại</a>
    </div>
</form>
