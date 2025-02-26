<?php
session_start();
include_once '../../../config/database.php';

// Lấy danh sách user để chọn user_id
$userQuery = "SELECT id, username FROM users";
$userResult = $conn->query($userQuery);
$users = [];
if ($userResult->num_rows > 0) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}
// Lấy danh sách chức vụ
$positionQuery = "SELECT id, position_name FROM positions";
$positionResult = $conn->query($positionQuery);
$positions = [];
if ($positionResult->num_rows > 0) {
    while ($row = $positionResult->fetch_assoc()) {
        $positions[] = $row;
    }
}

// Lấy danh sách phòng ban
$departmentQuery = "SELECT id, department_name FROM departments";
$departmentResult = $conn->query($departmentQuery);
$departments = [];
if ($departmentResult->num_rows > 0) {
    while ($row = $departmentResult->fetch_assoc()) {
        $departments[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $employee_name = $_POST['employee_name'];
    $employee_email = $_POST['employee_email'];
    $phone_number = $_POST['phone_number'];
    $position = $_POST['position'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    $department_id = $_POST['department_id'];

    // Kiểm tra user_id có tồn tại trong bảng employees không
    $checkQuery = "SELECT id FROM employees WHERE user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $numRows = $stmt->num_rows; // Lưu lại số hàng
    $stmt->close(); // Đóng statement

    if ($numRows > 0) {
        // Nếu user_id đã tồn tại, thực hiện UPDATE
        $updateQuery = "UPDATE employees 
                        SET employee_name=?, employee_email=?, phone_number=?, position=?, address=?, city=?, state=?, postal_code=?, country=?, department_id=? 
                        WHERE user_id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssssssssi", $employee_name, $employee_email, $phone_number, $position, $address, $city, $state, $postal_code, $country, $department_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cập nhật nhân viên thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật nhân viên: ' . $stmt->error]);
        }
    } else {
        // Nếu user_id chưa tồn tại, thực hiện INSERT
        $insertQuery = "INSERT INTO employees (user_id, employee_name, employee_email, phone_number, position, address, city, state, postal_code, country, department_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isssssssssi", $user_id, $employee_name, $employee_email, $phone_number, $position, $address, $city, $state, $postal_code, $country, $department_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Thêm nhân viên thành công!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm nhân viên: ' . $stmt->error]);
        }
    }
    $stmt->close();
    $conn->close();
    exit;
}

?>

<h2>Thông Tin Nhân Viên</h2>
<form id="add-employee-form" method="POST">
    <div class="form-container">
        <div class="form-column">
            <label for="user_id">Chọn User ID:</label>
            <select name="user_id" id="user_id" required>
                <option value="">-- Chọn User --</option>
                <?php foreach ($users as $user) { ?>
                    <option value="<?= $user['id'] ?>"><?= $user['username'] ?> (ID: <?= $user['id'] ?>)</option>
                <?php } ?>
            </select>

            <label for="employee_name">Tên nhân viên:</label>
            <input type="text" name="employee_name" id="employee_name" required>

            <label for="employee_email">Email:</label>
            <input type="email" name="employee_email" id="employee_email" required>

          

<label for="department_id">Phòng ban:</label>
<select name="department_id" id="department_id" required>
    <option value="">-- Chọn phòng ban --</option>
    <?php foreach ($departments as $department) { ?>
        <option value="<?= $department['id'] ?>"><?= $department['department_name'] ?></option>
    <?php } ?>
</select>

<label for="position">Chức vụ:</label>
<select name="position" id="position" required>
    <option value="">-- Chọn chức vụ --</option>
</select>


        </div>

        <div class="form-column">
            <label for="address">Địa chỉ:</label>
            <input type="text" name="address" id="address">

            <label for="city">Thành phố:</label>
            <input type="text" name="city" id="city">

            <label for="state">Tỉnh/Bang:</label>
            <input type="text" name="state" id="state">

            <label for="postal_code">Mã bưu điện:</label>
            <input type="text" name="postal_code" id="postal_code">

            <label for="country">Quốc gia:</label>
            <input type="text" name="country" id="country">
        </div>

       <label for="phone_number">Số điện thoại:</label>
            <input type="text" name="phone_number" id="phone_number" required>
        </select>
    </div>

    <button type="submit">Lưu Nhân Viên</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#add-employee-form').submit(function(e) {
    e.preventDefault(); // Ngăn form gửi theo cách truyền thống

    const user_id = $('#user_id').val();
    const employee_name = $('#employee_name').val();
    const employee_email = $('#employee_email').val();
    const phone_number = $('#phone_number').val();
    const position = $('#position').val();
    const address = $('#address').val();
    const city = $('#city').val();
    const state = $('#state').val();
    const postal_code = $('#postal_code').val();
    const country = $('#country').val();
    const department_id = $('#department_id').val();

    $.ajax({
        url: 'dashboard/profile/update_employee.php',
        method: 'POST',
        data: {
            user_id: user_id,
            employee_name: employee_name,
            employee_email: employee_email,
            phone_number: phone_number,
            position: position,
            address: address,
            city: city,
            state: state,
            postal_code: postal_code,
            country: country,
            department_id: department_id
        },
        success: function(response) {
            try {
                const result = JSON.parse(response);
                alert(result.message);

                if (result.status === 'success') {
                    loadEmployees(); // Gọi hàm tải lại danh sách nhân viên
                    $('#add-employee-form')[0].reset(); // Reset form sau khi thêm
                }
            } catch (e) {
                console.error("Lỗi khi parse JSON:", e, response);
                alert("Lỗi phản hồi từ server.");
            }
        },
        error: function() {
            alert('Có lỗi khi cập nhật nhân viên.');
        }
    });
});

// Hàm tải lại danh sách nhân viên
function loadEmployees() {
    $.ajax({
        url: 'dashboard/profile/profile.php', // Tải lại danh sách nhân viên
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách nhân viên.');
        }
    });
}
$(document).ready(function() {
    $('#department_id').change(function() {
        let departmentId = $(this).val();

        if (departmentId) {
            $.ajax({
                url: 'dashboard/profile/get_positions.php',
                method: 'GET',
                data: { department_id: departmentId },
                dataType: 'json',
                success: function(data) {
                    let positionSelect = $('#position');
                    positionSelect.empty();
                    positionSelect.append('<option value="">-- Chọn chức vụ --</option>');

                    if (data.length > 0) {
                        data.forEach(function(position) {
                            positionSelect.append('<option value="' + position.id + '">' + position.position_name + '</option>');
                        });
                    } else {
                        positionSelect.append('<option value="">Không có chức vụ</option>');
                    }
                },
                error: function() {
                    alert('Lỗi khi tải danh sách chức vụ.');
                }
            });
        } else {
            $('#position').html('<option value="">-- Chọn chức vụ --</option>');
        }
    });
});

</script>
<style>


.form-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 700px;
    margin: 0 auto;
}

.form-column {
    width: 48%;
}

label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

input, select {
    width: 90%;
    padding: 8px;
    margin: 10px 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}


button {
    width: 50%;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    margin:0 auto;
    transition: background 0.3s;
}

button:hover {
    background-color: #218838;
}

@media (max-width: 600px) {
    .form-container {
        flex-direction: column;
    }
    .form-column {
        width: 100%;
    }
}

</style>