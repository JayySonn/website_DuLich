<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// LÍNH GÁC MỚI: Đẩy về đúng cửa login_admin.php
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('CẢNH BÁO: Bạn chưa đăng nhập trang Quản trị!'); window.location.href='login_admin.php';</script>";
    exit(); 
}

include 'db.php'; 

// 1. LẤY DỮ LIỆU CŨ CỦA TOUR
if (isset($_GET['id'])) {
    // VÁ LỖ HỔNG: Bắt buộc dùng intval() thay vì mysqli_real_escape_string cho biến là số
    $id = intval($_GET['id']); 
    
    $sql_get = "SELECT * FROM Tours WHERE TourID = $id";
    $result = mysqli_query($conn, $sql_get);
    $tour = mysqli_fetch_assoc($result);
    
    if (!$tour) {
        die("Không tìm thấy tour này!");
    }
} else {
    header("Location: admin_list_tours.php");
    exit();
}

// 2. XỬ LÝ LƯU CẬP NHẬT
if (isset($_POST['btnUpdate'])) {
    // Ép kiểu SỐ nguyên bằng intval() cho các cột dữ liệu số
    $cat_id = intval($_POST['category_id']);
    $price = intval($_POST['price']);
    $max_people = intval($_POST['max_people']); 
    
    // Dùng escape cho các cột dữ liệu CHỮ
    $name = mysqli_real_escape_string($conn, $_POST['tour_name']);
    $duration = mysqli_real_escape_string($conn, $_POST['duration']);
    $image = mysqli_real_escape_string($conn, $_POST['image_url']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $departure_schedule = mysqli_real_escape_string($conn, $_POST['departure_schedule']);
    $departure_location = mysqli_real_escape_string($conn, $_POST['departure_location']);

    // CẬP NHẬT CÂU LỆNH SQL
    $sql_update = "UPDATE Tours SET 
                    TourName = '$name', 
                    CategoryID = $cat_id, 
                    Price = $price, 
                    MaxPeople = $max_people, 
                    Duration = '$duration', 
                    ImageURL = '$image', 
                    Description = '$desc',
                    DepartureSchedule = '$departure_schedule',
                    DepartureLocation = '$departure_location' 
                   WHERE TourID = $id";
    
    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Cập nhật tour thành công!'); window.location='admin_list_tours.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Tour: <?php echo htmlspecialchars($tour['TourName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .edit-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .navbar-admin { background: #212529 !important; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-admin mb-5 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="admin_bookings.php">ADMIN DASHBOARD</a>
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link" href="admin_list_tours.php">Danh sách Tour</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card edit-card p-4">
                    <h2 class="text-center fw-bold text-primary mb-4">CHỈNH SỬA THÔNG TIN TOUR</h2>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên Tour:</label>
                            <input type="text" name="tour_name" class="form-control" value="<?php echo htmlspecialchars($tour['TourName']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Danh mục:</label>
                                <select name="category_id" class="form-select">
                                    <?php 
                                    $res_cat = mysqli_query($conn, "SELECT * FROM Categories");
                                    while($cat = mysqli_fetch_assoc($res_cat)) {
                                        $selected = ($cat['CategoryID'] == $tour['CategoryID']) ? "selected" : "";
                                        echo "<option value='".$cat['CategoryID']."' $selected>".htmlspecialchars($cat['CategoryName'])."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Giá tiền (VNĐ):</label>
                                <input type="number" name="price" class="form-control" value="<?php echo $tour['Price']; ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Số khách tối đa:</label>
                                <input type="number" name="max_people" class="form-control" value="<?php echo isset($tour['MaxPeople']) ? $tour['MaxPeople'] : 10; ?>" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Thời lượng:</label>
                                <input type="text" name="duration" class="form-control" value="<?php echo htmlspecialchars($tour['Duration']); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Lịch khởi hành:</label>
                                <input type="text" name="departure_schedule" class="form-control" value="<?php echo htmlspecialchars($tour['DepartureSchedule']); ?>" placeholder="Ví dụ: Thứ 7 hàng tuần">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Điểm khởi hành:</label>
                                <input type="text" name="departure_location" class="form-control" value="<?php echo htmlspecialchars($tour['DepartureLocation']); ?>" placeholder="Ví dụ: TP.HCM">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Link ảnh:</label>
                                <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($tour['ImageURL']); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Mô tả & Lịch trình:</label>
                            <textarea name="description" class="form-control" rows="6"><?php echo htmlspecialchars($tour['Description']); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="btnUpdate" class="btn btn-primary btn-lg fw-bold rounded-pill shadow-sm">
                                <i class="bi bi-save me-2"></i>LƯU THAY ĐỔI
                            </button>
                            <a href="admin_list_tours.php" class="btn btn-outline-secondary rounded-pill">Hủy bỏ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>