<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// LÍNH GÁC MỚI: Kiểm tra thẻ 'admin_id' và đẩy về đúng cửa login_admin.php
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('CẢNH BÁO: Bạn chưa đăng nhập trang Quản trị!'); window.location.href='login_admin.php';</script>";
    exit(); 
}
include 'db.php';

if(isset($_POST['btnThem'])) {
    // VÁ LỖ HỔNG: Dùng intval() cho các dữ liệu là CON SỐ để chống SQL Injection
    $cat_id = intval($_POST['category_id']);
    $price = intval($_POST['price']);
    $max_people = intval($_POST['max_people']); 
    
    // Dùng mysqli_real_escape_string cho các dữ liệu là CHỮ
    $name = mysqli_real_escape_string($conn, $_POST['tour_name']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $image = mysqli_real_escape_string($conn, $_POST['image_url']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $departure_schedule = mysqli_real_escape_string($conn, $_POST['departure_schedule']);
    $departure_location = mysqli_real_escape_string($conn, $_POST['departure_location']);

    // CẬP NHẬT CÂU LỆNH SQL
    $sql = "INSERT INTO Tours (TourName, CategoryID, Price, MaxPeople, Duration, ImageURL, Description, DepartureSchedule, DepartureLocation) 
            VALUES ('$name', '$cat_id', '$price', '$max_people', '$duration', '$image', '$desc', '$departure_schedule', '$departure_location')";

    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('Thêm tour mới thành công! Giới hạn: $max_people khách.'); window.location='admin_list_tours.php';</script>";
    } else {
        echo "Lỗi hệ thống: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Thêm Tour Mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Chống nhảy menu khi chuyển trang */
        html { overflow-y: scroll; }
        
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .admin-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-add { border-radius: 10px; padding: 12px; font-weight: 600; }
        .form-label { font-weight: 600; color: #495057; }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mb-5 mt-4"> 
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card admin-card">
                <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                    <h2 class="fw-bold text-primary">ĐĂNG TOUR DU LỊCH MỚI</h2>
                    <p class="text-muted">Điền đầy đủ thông tin bên dưới để hiển thị lên trang chủ</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tên Tour Du Lịch:</label>
                            <input type="text" name="tour_name" class="form-control rounded-3" placeholder="Ví dụ: Tour Miền Tây Sông Nước 2 Ngày" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Danh mục loại hình:</label>
                                <select name="category_id" class="form-select rounded-3" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php 
                                        $res_cat = mysqli_query($conn, "SELECT * FROM Categories");
                                        while($cat = mysqli_fetch_assoc($res_cat)) {
                                            echo "<option value='".$cat['CategoryID']."'>".$cat['CategoryName']."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Giá Tour (VNĐ):</label>
                                <input type="number" name="price" class="form-control rounded-3" placeholder="3500000" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Số khách tối đa:</label>
                                <input type="number" name="max_people" class="form-control rounded-3" value="10" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Thời gian:</label>
                                <input type="text" name="duration" class="form-control rounded-3" placeholder="Ví dụ: 3 ngày 2 đêm">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Lịch khởi hành:</label>
                                <input type="text" name="departure_schedule" class="form-control rounded-3" placeholder="Ví dụ: Thứ 7 hàng tuần">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Điểm khởi hành:</label>
                                <input type="text" name="departure_location" class="form-control rounded-3" placeholder="Ví dụ: Mỹ Tho, Tiền Giang">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Đường dẫn ảnh (URL):</label>
                                <input type="text" name="image_url" class="form-control rounded-3" placeholder="https://anh-dep.jpg">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Lịch trình & Mô tả chi tiết:</label>
                            <textarea name="description" class="form-control rounded-3" rows="6" placeholder="Nhập lịch trình từng ngày tại đây..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="btnThem" class="btn btn-primary btn-add shadow">
                                <i class="bi bi-cloud-upload me-2"></i>ĐĂNG TOUR LÊN WEBSITE
                            </button>
                            <a href="admin_list_tours.php" class="btn btn-outline-dark btn-add">
                                <i class="bi bi-list-task me-2"></i>QUAY LẠI DANH SÁCH TOUR
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center pb-4 text-muted small">
  © 2026 Hệ thống quản trị MienTay Travel - Admin
</footer>

</body>
</html>