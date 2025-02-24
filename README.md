# Website Quản Lý Phân Công

## Giới Thiệu
Dự án **Website Quản Lý Phân Công** được xây dựng bằng PHP nhằm hỗ trợ quản lý công việc, phân công nhân sự và theo dõi tiến độ công việc một cách hiệu quả.

## Chức Năng Chính
### 1. Quản Lý Sản Phẩm
- Hiển thị danh sách sản phẩm theo danh mục.
- Chi tiết sản phẩm: tên, giá, mô tả, kích thước và hình ảnh.

### 2. Tìm Kiếm và Lọc Sản Phẩm
- Tìm kiếm sản phẩm theo từ khóa.
- Lọc sản phẩm theo tên, danh mục hoặc thương hiệu.

### 3. Giỏ Hàng
- Thêm sản phẩm vào giỏ hàng.
- Cập nhật số lượng hoặc xóa sản phẩm trong giỏ.

### 4. Đặt Hàng và Thanh Toán
- Hỗ trợ đặt hàng.
- Lưu thông tin đơn hàng.

### 5. Quản Trị Viên
- Quản lý sản phẩm (thêm, sửa, xóa).
- Quản lý danh mục sản phẩm.
- Xem danh sách đơn hàng và trạng thái.
- Quản lý người dùng (thêm, sửa, xóa).
- Quản lý phòng ban, vị trí, vai trò.
- Phân công công việc cho nhân sự.
- Xem tổng quan công việc.

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
git clone https://github.com/your-repo-name.git
cd your-repo-name
```

#### b. Cấu Hình Database
- Tạo database trong MySQL.
- Cập nhật file `.env` (nếu dùng Laravel) hoặc file cấu hình PHP với thông tin database.

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

## Giấy Phép
Dự án này được phát hành theo giấy phép MIT.
