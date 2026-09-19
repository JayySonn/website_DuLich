<?php 
session_start();

// Kiểm tra quyền Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); 
    exit();
}

include 'db.php'; 

// Xử lý xóa tour
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']); // Dùng intval để bảo mật hơn
    $sql_delete = "DELETE FROM Tours WHERE TourID = $id";
    if(mysqli_query($conn, $sql_delete)) {
        echo "<script>alert('Đã xóa tour thành công!'); window.location='admin_list_tours.php';</script>";
    } else {
        echo "Lỗi xóa: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .tour-img-admin { width: 70px; height: 45px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .table-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .navbar-dark { background-color: #212529 !important; }
        .table thead { background-color: #f1f3f5; }
        .btn-action { border-radius: 8px; transition: 0.2s; }
    </style>
</head>
<body>

    <?php include 'navbar_admin.php'; ?>

    <div class="container mt-4">
        <div class="table-container border-0">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-0 text-dark">Quản Lý Sản Phẩm Tour</h3>
                    <p class="text-muted small mb-0">Danh sách các tour đang hiển thị trên website</p>
                </div>
                <a href="admin_add_tour.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-plus-lg"></i> Thêm Tour Mới
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>STT</th> 
                            <th>Hình ảnh</th>
                            <th>Tên Tour</th>
                            <th>Giá Tiền</th>
                            <th>Thông tin khởi hành</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM Tours ORDER BY TourID DESC";
                        $result = mysqli_query($conn, $sql);
                        $stt = 1;

                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td class="text-muted fw-bold">#<?php echo $stt++; ?></td>
                                    <td>
                                        <img src="<?php echo (!empty($row['ImageURL'])) ? htmlspecialchars($row['ImageURL']) : 'https://placehold.co/100x70?text=No+Image'; ?>" 
                                             class="tour-img-admin shadow-sm" 
                                             onerror="this.src='https://placehold.co/100x70?text=Error+Image'">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['TourName']); ?></div>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold"><?php echo number_format($row['Price']); ?>đ</span>
                                    </td>
                                    <td>
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-clock"></i> <?php echo htmlspecialchars($row['Duration']); ?>
                                        </div>
                                        <div class="small fw-bold text-primary mb-1">
                                            <i class="bi bi-calendar-event"></i> 
                                            <?php echo (!empty($row['DepartureSchedule'])) ? htmlspecialchars($row['DepartureSchedule']) : "Hàng ngày"; ?>
                                        </div>
                                        <div class="small text-secondary">
                                            <i class="bi bi-geo-alt"></i> Khởi hành: 
                                            <?php echo (!empty($row['DepartureLocation'])) ? htmlspecialchars($row['DepartureLocation']) : "Chưa cập nhật"; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- NÚT QUẢN LÝ THƯ VIỆN ẢNH (MỚI THÊM) -->
                                            <a href="admin_tour_images.php?tour_id=<?php echo $row['TourID']; ?>" 
                                               class="btn btn-sm btn-outline-info btn-action" 
                                               title="Quản lý thư viện ảnh phụ">
                                                <i class="bi bi-images"></i>
                                            </a>

                                            <a href="admin_edit_tour.php?id=<?php echo $row['TourID']; ?>" 
                                               class="btn btn-sm btn-outline-primary btn-action"
                                               title="Sửa tour">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <a href="admin_list_tours.php?delete_id=<?php echo $row['TourID']; ?>" 
                                               class="btn btn-sm btn-outline-danger btn-action" 
                                               onclick="return confirm('Sơn có chắc chắn muốn xóa tour này không?')"
                                               title="Xóa tour">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted fst-italic'>Hiện chưa có tour nào.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 py-4 text-muted small">
        © 2026 Hệ thống quản trị MienTay Travel - Thiết kế bởi Sơn
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>