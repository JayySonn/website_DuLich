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

// --- TẠO MÃ BẢO MẬT CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- XỬ LÝ XÓA TOUR (Dùng POST để chống hack CSRF) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật! Yêu cầu không hợp lệ.'); window.location.href='admin_list_tours.php';</script>");
    }

    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']); 
        
        $check_booking = mysqli_query($conn, "SELECT * FROM Bookings WHERE TourID = $id");
        if (mysqli_num_rows($check_booking) > 0) {
            echo "<script>alert('LỖI: Không thể xóa! Tour này đang có khách đặt. Vui lòng hủy các đơn đặt tour trước.'); window.location='admin_list_tours.php';</script>";
        } else {
            $sql_delete = "DELETE FROM Tours WHERE TourID = $id";
            if(mysqli_query($conn, $sql_delete)) {
                echo "<script>alert('Đã xóa tour thành công!'); window.location='admin_list_tours.php';</script>";
            } else {
                echo "<script>alert('Lỗi xóa: " . mysqli_error($conn) . "'); window.location='admin_list_tours.php';</script>";
            }
        }
    }
}

// --- TÌM KIẾM & PHÂN TRANG ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$condition = "1=1";
if (!empty($search)) {
    $condition .= " AND TourName LIKE '%$search%'";
}

$limit = 10; // Hiển thị 10 tour 1 trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM Tours WHERE $condition";
$res_count = mysqli_query($conn, $sql_count);
$total_records = mysqli_fetch_assoc($res_count)['total'];
$total_pages = ceil($total_records / $limit);
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
        .tour-img-admin { width: 120px; height: 75px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
        .table-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .navbar-dark { background-color: #212529 !important; }
        .table thead { background-color: #f1f3f5; }
        .btn-action { border-radius: 8px; transition: 0.2s; }
        
        /* Hiệu ứng nhấp nháy cho badge hết hạn */
        @keyframes flash {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .badge-expired { animation: flash 2s infinite; }
    </style>
</head>
<body>

    <?php include 'navbar_admin.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="table-container border-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold mb-0 text-dark">Quản Lý Sản Phẩm Tour</h3>
                    <p class="text-muted small mb-0">Danh sách các tour đang hiển thị trên website</p>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <!-- Form Tìm Kiếm -->
                    <form method="GET" action="admin_list_tours.php" class="m-0">
                        <div class="input-group shadow-sm">
                            <input type="text" name="search" class="form-control" placeholder="Tìm tên tour..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary bg-white" type="submit"><i class="bi bi-search"></i></button>
                            <?php if(!empty($search)): ?>
                                <a href="admin_list_tours.php" class="btn btn-danger"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <a href="admin_add_tour.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold text-nowrap">
                        <i class="bi bi-plus-lg"></i> Thêm Tour Mới
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>STT</th> 
                            <th>Hình ảnh</th>
                            <th width="30%">Tên Tour</th>
                            <th>Giá Tiền</th>
                            <th width="25%">Thông tin khởi hành</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM Tours WHERE $condition ORDER BY TourID DESC LIMIT $offset, $limit";
                        $result = mysqli_query($conn, $sql);
                        $stt = $offset + 1; // STT nối tiếp khi qua trang mới

                        if (mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                
                                // THUẬT TOÁN QUÉT NGÀY HẾT HẠN
                                $schedule = $row['DepartureSchedule'];
                                $is_expired = false;
                                if (!empty($schedule)) {
                                    $date_parse = str_replace('/', '-', $schedule);
                                    $tour_time = strtotime($date_parse);
                                    if ($tour_time !== false && $tour_time < strtotime('today')) {
                                        $is_expired = true;
                                    }
                                }
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
                                        <?php if ($is_expired): ?>
                                            <span class="badge bg-danger mt-1 badge-expired"><i class="bi bi-exclamation-triangle-fill"></i> Đã hết hạn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-bold"><?php echo number_format($row['Price']); ?>đ</span>
                                    </td>
                                    
                                    <!-- Làm mờ cột thông tin nếu đã hết hạn -->
                                    <td style="<?php echo $is_expired ? 'opacity: 0.5;' : ''; ?>">
                                        <div class="small text-muted mb-1">
                                            <i class="bi bi-clock"></i> <?php echo htmlspecialchars($row['Duration']); ?>
                                        </div>
                                        <div class="small fw-bold text-primary mb-1 <?php echo $is_expired ? 'text-decoration-line-through text-danger' : ''; ?>">
                                            <i class="bi bi-calendar-event"></i> 
                                            <?php echo (!empty($row['DepartureSchedule'])) ? htmlspecialchars($row['DepartureSchedule']) : "Theo yêu cầu"; ?>
                                        </div>
                                        <div class="small text-secondary">
                                            <i class="bi bi-geo-alt"></i> Nơi xuất phát: 
                                            <?php echo (!empty($row['DepartureLocation'])) ? htmlspecialchars($row['DepartureLocation']) : "Chưa cập nhật"; ?>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="admin_tour_images.php?tour_id=<?php echo $row['TourID']; ?>" class="btn btn-sm btn-outline-info btn-action" title="Quản lý thư viện ảnh phụ">
                                                <i class="bi bi-images"></i>
                                            </a>
                                            <a href="admin_edit_tour.php?id=<?php echo $row['TourID']; ?>" class="btn btn-sm btn-outline-primary btn-action" title="Sửa tour">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form method="POST" class="d-inline m-0 p-0">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['TourID']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger btn-action" title="Xóa tour" onclick="return confirm('Sơn có chắc chắn muốn xóa tour này không?');">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted fst-italic'>Không tìm thấy dữ liệu tour.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- THANH ĐIỀU HƯỚNG PHÂN TRANG -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center mb-0">
                        <?php 
                        $qs = "";
                        if(!empty($search)) $qs = "&search=" . urlencode($search);
                        ?>
                        
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1 . $qs; ?>">Trước</a>
                        </li>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i . $qs; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1 . $qs; ?>">Sau</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>
    </div>

    <footer class="text-center mt-5 py-4 text-muted small">
        © 2026 Quản trị MiềnTây Travel 
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>