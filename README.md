# Website Quản Lý Phân Công

## Giới Thiệu
Dự án **Website Quản Lý Phân Công** được xây dựng bằng PHP nhằm hỗ trợ quản lý công việc, phân công nhân sự và theo dõi tiến độ công việc một cách hiệu quả.

## Chức Năng Chính
### 1. Quản Lý Người Dùng
- Thêm, sửa, xóa người dùng.
- Quản lý thông tin cá nhân và quyền hạn.

### 2. Quản Lý Phòng Ban
- Thêm, sửa, xóa phòng ban.
- Phân loại và tổ chức các phòng ban theo cấu trúc công ty.

### 3. Quản Lý Vị Trí và Vai Trò
- Định nghĩa vai trò, quyền hạn của từng vị trí.
- Phân quyền quản trị hệ thống.

### 4. Quản Lý Phân Công Công Việc
- Giao việc cho nhân sự.
- Theo dõi tiến độ công việc.
- Cập nhật trạng thái công việc.

### 5. Tổng Quan Công Việc
- Xem danh sách công việc theo phòng ban, nhân sự.
- Báo cáo tình trạng hoàn thành công việc.

## Công Nghệ Sử Dụng
- **Ngôn ngữ**: PHP
- **Cơ sở dữ liệu**: MySQL (PhpMyAdmin)
- **Frontend**: HTML, CSS, JavaScript
- **Framework**: Laravel (nếu có)

## Cài Đặt và Chạy Dự Án
### 1. Yêu Cầu Hệ Thống
- PHP >= 7.4
- MySQL
- Apache hoặc Nginx
- Composer (nếu sử dụng Laravel)

### 2. Cài Đặt
#### a. Clone Dự Án
```bash
git clone https://github.com/Heliophile0208/QuanLyPhanCong.git
cd QuanLyPhanCong
```

#### b. Cấu Hình Database
- Tạo database trong MySQL.
- Cập nhật file cấu hình PHP với thông tin database.

#### c. Cài Đặt Dependencies (nếu có Laravel)
```bash
composer install
```

#### d. Chạy Ứng Dụng
```bash
php -S localhost:8000
```
Truy cập: `http://localhost:8000`

## Đóng Góp
Nếu bạn muốn đóng góp vào dự án, vui lòng tạo Pull Request hoặc mở Issue để thảo luận.
