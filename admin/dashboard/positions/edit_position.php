<style>
    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    label {
        font-size: 14px;
        margin-bottom: 5px;
        display: block;
        font-weight: bold;
    }

    input[type="text"],
    select,
    button {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    select:focus {
        border-color: #4CAF50;
    }

    button {
        background-color: #4CAF50;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border: none;
    }

    button:hover {
        background-color: #45a049;
    }
</style>

<?php
session_start();
include_once '../../../config/database.php';

if (isset($_GET['PositionID'])) {
    $PositionID = $_GET['PositionID'];
    $positionQuery = "SELECT * FROM positions WHERE id = ?";
    $stmt = $conn->prepare($positionQuery);
    $stmt->bind_param("i", $PositionID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $position = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy chức vụ.";
        exit;
    }
    ?>
<h2>Chỉnh sửa chức vụ</h2>
<form id="editPositionForm">
    <input type="hidden" name="PositionID" value="<?php echo $position['id']; ?>">

    <label for="position_name">Tên chức vụ:</label>
    <input type="text" id="position_name" name="position_name" value="<?php echo htmlspecialchars($position['position_name']); ?>" required><br>

    <label for="description">Mô tả:</label>
    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($position['description']); ?>" required><br>

    <button type="submit">Cập nhật</button>
</form>
<?php
} else {
    echo "Không có PositionID để sửa.";
}
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById("editPositionForm").addEventListener("submit", function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '/admin/dashboard/positions/update_position.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.trim() === "success") {
                    alert("Cập nhật thành công!");
                    $('#dashboard-content').load('/admin/dashboard/positions/positions.php');
                } else {
                    alert("Có lỗi xảy ra: " + response);
                }
            },
            error: function() {
                alert("Lỗi kết nối tới máy chủ!");
            }
        });
    });
</script>