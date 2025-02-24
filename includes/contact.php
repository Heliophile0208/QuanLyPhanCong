<?php session_start();
include_once 'header.php'; // Kết nối header và CSS
?>


<div class="contact-container">
    <h2>Liên hệ với chúng tôi</h2>

<?php if (isset($_SESSION['contact_success'])): ?>
    <div class="alert success"><?= $_SESSION['contact_success']; ?></div>
    <?php unset($_SESSION['contact_success']); ?>
<?php elseif (isset($_SESSION['contact_error'])): ?>
    <div class="alert error"><?= $_SESSION['contact_error']; ?></div>
    <?php unset($_SESSION['contact_error']); ?>
<?php endif; ?>

    <form action="process_contact.php" method="POST" class="contact-form">
        <div class="form-group">
            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="message">Tin nhắn</label>
            <textarea id="message" name="message" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn-submit">Gửi Tin Nhắn</button>
    </form>
</div>

  <div class="contact-info">
    <h3>Thông tin liên hệ:</h3>
    <p><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> 
        <a href="https://www.google.com/maps/place/WORKSHOP+NIDTECH/@10.9371704,106.8237158,17z/data=!3m1!4b1!4m6!3m5!1s0x3174df003e627e43:0x566006207bd6b236!8m2!3d10.9371651!4d106.8262907!16s%2Fg%2F11lnk4ggpw?entry=ttu&g_ep=EgoyMDI0MTEyNC4xIKXMDSoASAFQAw%3D%3D">
        8 Đ. Nguyễn Thành Phương, Trung Dũng, Biên Hòa, Đồng Nai</a>
    </p>
    
    <p><i class="fas fa-phone-alt"></i> <strong>Số điện thoại:</strong> 
        <a href="tel:0947228844">0947228844</a>
    </p>

    <p><i class="fas fa-envelope"></i> <strong>Email:</strong> 
        <a href="mailto:nidtech.vn@gmail.com">nidtech.vn@gmail.com</a>
    </p>
</div>


    <div class="contact-map">
        <h3>Vị trí của chúng tôi</h3>
   <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3917.3466568330346!2d106.8262907!3d10.9371651!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174df003e627e43%3A0x566006207bd6b236!2sWORKSHOP%20NIDTECH!5e0!3m2!1sfr!2s!4v1740384891630!5m2!1sfr!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>
<link rel="stylesheet" href="../css/contact.css" type="text/css">
<style>
/* Vùng thông tin liên hệ */
.contact-info {
    margin-top: 30px;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    text-align: left;
}
.alert {
    padding: 15px;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    margin-top: 20px;
}

.success {
    color:  #28a745;
    font-size:20px;
}

.error {
    font-size:20px;
    color:  #dc3545;
}

/* Tiêu đề */
.contact-info h3 {
    font-size: 22px;
    margin-bottom: 15px;
    color: #333;
    text-align: center;
}

/* Các dòng thông tin */
.contact-info p {
    font-size: 16px;
    margin-bottom: 10px;
    line-height: 1.5;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Định dạng chữ đậm cho tiêu đề */
.contact-info p strong {
    font-weight: bold;
    color: #444;
    min-width: 120px; /* Cố định chiều rộng để căn chỉnh */
}

/* Link liên hệ */
.contact-info a {
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease-in-out;
}

.contact-info a:hover {
    color: #0056b3;
    text-decoration: underline;
}

/* Căn chỉnh icon (nếu cần) */
.contact-info p i {
    color: #007bff;
    font-size: 18px;
}</style>
