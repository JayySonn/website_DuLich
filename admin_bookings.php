<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// LÍNH GÁC MỚI: Kiểm tra thẻ 'admin_id' và đẩy về đúng trang login_admin.php
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('CẢNH BÁO: Bạn chưa đăng nhập trang Quản trị!'); window.location.href='login_admin.php';</script>";
    exit(); 
}

// Cấu hình đường dẫn PHPMailer
$base_path = dirname(__DIR__) . '/libs/PHPMailer/src/';
require $base_path . 'Exception.php';
require $base_path . 'PHPMailer.php';
require $base_path . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db.php';

// --- 1. TẠO MÃ BẢO MẬT CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- 2. XỬ LÝ POST: DUYỆT ĐƠN & XÓA ĐƠN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật! Yêu cầu không hợp lệ.'); window.location.href='admin_bookings.php';</script>");
    }

    if (isset($_POST['action']) && isset($_POST['booking_id'])) {
        $id = intval($_POST['booking_id']);

        if ($_POST['action'] === 'approve') {
            $sql_info = "SELECT Bookings.*, Tours.TourName, Users.Email 
                         FROM Bookings 
                         JOIN Tours ON Bookings.TourID = Tours.TourID 
                         JOIN Users ON Bookings.UserID = Users.UserID 
                         WHERE BookingID = $id";
            $res_info = mysqli_query($conn, $sql_info);
            $data = mysqli_fetch_assoc($res_info);

            if ($data) {
                $sql_update = "UPDATE Bookings SET Status = 'Đã duyệt' WHERE BookingID = $id";
                if (mysqli_query($conn, $sql_update)) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'joji19982@gmail.com'; 
                        $mail->Password   = 'rnvw xhrv ypit dqua';      
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';
                        $mail->setFrom('no-reply@mientaytravel.com', 'MIỀNTÂY TRAVEL');
                        $mail->addAddress($data['Email'], $data['CustomerName']);
                        $mail->isHTML(true);
                        $mail->Subject = 'XÁC NHẬN ĐẶT TOUR THÀNH CÔNG - ĐƠN HÀNG #' . $id;
                        $mail->Body    = "<div style='font-family: sans-serif; border: 1px solid #eee; padding: 20px; border-radius: 10px;'><h2 style='color: #198754;'>Chào {$data['CustomerName']},</h2><p>Đơn đặt tour <b>{$data['TourName']}</b> của bạn đã được Xác nhận thành công!</p></div>";
                        $mail->send();
                    } catch (Exception $e) { }
                }
            }
        } 
        elseif ($_POST['action'] === 'delete') {
            $sql_delete = "DELETE FROM Bookings WHERE BookingID = ?";
            $stmt = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }
        
        // Giữ lại tham số phân trang & lọc khi reload
        $redirect_url = "admin_bookings.php?" . http_build_query($_GET);
        header("Location: $redirect_url");
        exit();
    }
}

// --- 3. LẤY DỮ LIỆU LỌC TỪ URL ---
$search_val = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_val = $_GET['status'] ?? '';
$date_val   = $_GET['date'] ?? '';

// --- 4. TẠO CHUỖI ĐIỀU KIỆN LỌC (Tái sử dụng cho đếm số dòng và truy vấn data) ---
$filter_sql = "";
if ($search_val !== '') {
    $search_safe = mysqli_real_escape_string($conn, $search_val);
    if (is_numeric($search_val)) {
        $filter_sql .= " AND Bookings.Phone LIKE '%$search_safe%'";
    } elseif (mb_strtolower($search_val, 'UTF-8') == 'an' || mb_strtolower($search_val, 'UTF-8') == 'anh') {
        $filter_sql .= " AND Bookings.CustomerName = N'$search_safe'";
    } else {
        $filter_sql .= " AND Bookings.CustomerName LIKE N'%$search_safe%'";
    }
}
if ($status_val !== '') {
    $status_safe = mysqli_real_escape_string($conn, $status_val);
    $filter_sql .= " AND Bookings.Status = '$status_safe'"; 
}
if (!empty($date_val)) {
    $date_safe = mysqli_real_escape_string($conn, $date_val);
    $filter_sql .= " AND DATE(Bookings.BookingDate) = '$date_safe'";
}

// --- 5. THUẬT TOÁN PHÂN TRANG ---
$limit = 10; // Số đơn hàng muốn hiển thị trên 1 trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số đơn hàng khớp với bộ lọc
$sql_count = "SELECT COUNT(*) AS total FROM Bookings JOIN Tours ON Bookings.TourID = Tours.TourID WHERE 1=1 " . $filter_sql;
$res_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_records = $row_count['total'];
$total_pages = ceil($total_records / $limit);

// Lấy dữ liệu theo Limit và Offset
$sql = "SELECT Bookings.*, Tours.TourName, Tours.DepartureLocation 
        FROM Bookings 
        JOIN Tours ON Bookings.TourID = Tours.TourID 
        WHERE 1=1 " . $filter_sql . " 
        ORDER BY Bookings.BookingID DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Tạo query string để giữ nguyên bộ lọc khi bấm chuyển trang
$query_params = $_GET;
unset($query_params['page']); // Xóa biến page cũ để cập nhật biến page mới
$query_string = http_build_query($query_params);
if (!empty($query_string)) {
    $query_string = "&" . $query_string;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn đặt Tour - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .status-wait { color: #fd7e14; font-weight: bold; }
        .status-done { color: #198754; font-weight: bold; }
        .status-cancel { color: #dc3545; font-weight: bold; }
        .filter-section { background: #f1f3f5; border-radius: 10px; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mt-4">
    <div class="table-container">
        <h2 class="mb-4 fw-bold text-dark"><i class="bi bi-list-stars text-primary"></i> Danh Sách Đơn Đặt Tour</h2>
        
        <div class="filter-section">
            <form method="GET" action="admin_bookings.php" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tên khách hoặc SĐT..." value="<?php echo htmlspecialchars($search_val); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Chờ xác nhận" <?php if($status_val == 'Chờ xác nhận') echo 'selected'; ?>>Chờ xác nhận</option>
                        <option value="Đã duyệt" <?php if($status_val == 'Đã duyệt') echo 'selected'; ?>>Đã duyệt</option>
                        <option value="Đã hủy" <?php if($status_val == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_val); ?>">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Lọc</button>
                    <a href="admin_bookings.php" class="btn btn-outline-secondary w-100 pt-2 text-decoration-none text-center">Reset</a>
                </div>
            </form>
        </div>

        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Khách Hàng</th>
                    <th>Tour / Ngày Đi</th>
                    <th>Tổng Tiền</th>
                    <th>Ngày Đặt</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Số thứ tự tiếp nối theo từng trang (VD: Trang 2 bắt đầu từ 11)
                $stt = $offset + 1; 
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $statusClass = 'status-wait';
                        if($row['Status'] == 'Đã duyệt') $statusClass = 'status-done';
                        if($row['Status'] == 'Đã hủy') $statusClass = 'status-cancel';
                        ?>
                        <tr>
                            <td class="text-muted fw-bold">#<?php echo $stt++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['CustomerName']); ?></strong><br>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($row['Phone']); ?></small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['TourName']); ?></strong><br>
                                <div class="text-primary small"><i class="bi bi-calendar-check"></i> Đi: <?php echo date('d/m/Y', strtotime($row['DepartureDate'])); ?></div>
                            </td>
                            <td>
                                <div class="text-danger fw-bold"><?php echo number_format($row['TotalPrice']); ?>đ</div>
                            </td>
                            <td class="small text-muted"><?php echo date('d/m/Y', strtotime($row['BookingDate'])); ?></td>
                            <td><span class="<?php echo $statusClass; ?>"><?php echo $row['Status']; ?></span></td>
                            
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-outline-primary view-detail" 
                                        data-id="<?php echo $row['BookingID']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['CustomerName']); ?>"
                                        data-phone="<?php echo $row['Phone']; ?>"
                                        data-tour="<?php echo htmlspecialchars($row['TourName']); ?>"
                                        data-date="<?php echo date('d/m/Y', strtotime($row['DepartureDate'])); ?>"
                                        data-qty="<?php echo $row['Quantity']; ?>"
                                        data-price="<?php echo number_format($row['TotalPrice']); ?>đ"
                                        data-method="<?php echo htmlspecialchars($row['PaymentMethod']); ?>"
                                        data-status="<?php echo $row['Status']; ?>"
                                        data-bookingdate="<?php echo date('d/m/Y H:i', strtotime($row['BookingDate'])); ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Thêm page vào URL form để duyệt/xóa xong vẫn ở lại đúng trang đó -->
                                    <form method="POST" action="admin_bookings.php?page=<?php echo $page . $query_string; ?>" class="d-inline mb-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="booking_id" value="<?php echo $row['BookingID']; ?>">
                                        
                                        <?php if($row['Status'] == 'Chờ xác nhận'): ?>
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('Xác nhận duyệt đơn này?');">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center py-5 text-muted fst-italic'>Không có dữ liệu.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- THANH CHUYỂN TRANG (PAGINATION) -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Nút Previous -->
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>">&laquo; Trước</a>
                </li>
                
                <!-- In các số trang -->
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $query_string; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <!-- Nút Next -->
                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>">Sau &raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
    </div>
</div>

<footer class="text-center mt-5 py-4 text-muted small">
    © 2026 Quản trị MiềnTây Travel - Chào Sơn!
</footer>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Chi Tiết #<span id="dt-id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p><strong>Khách hàng:</strong> <span id="dt-name"></span></p>
                <p><strong>SĐT:</strong> <span id="dt-phone"></span></p>
                <hr>
                <p><strong>Tour:</strong> <span id="dt-tour" class="text-primary fw-bold"></span></p>
                <p><strong>Ngày đi:</strong> <span id="dt-date"></span></p>
                <p><strong>Số lượng:</strong> <span id="dt-qty"></span></p>
                <p><strong>Tổng tiền:</strong> <span id="dt-price" class="text-danger fw-bold"></span></p>
                <p><strong>Trạng thái:</strong> <span id="dt-status" class="badge"></span></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.view-detail').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('dt-id').innerText = this.dataset.id;
        document.getElementById('dt-name').innerText = this.dataset.name;
        document.getElementById('dt-phone').innerText = this.dataset.phone;
        document.getElementById('dt-tour').innerText = this.dataset.tour;
        document.getElementById('dt-date').innerText = this.dataset.date;
        document.getElementById('dt-qty').innerText = this.dataset.qty + ' người';
        document.getElementById('dt-price').innerText = this.dataset.price;
        let status = this.dataset.status;
        let statusEl = document.getElementById('dt-status');
        statusEl.innerText = status;
        statusEl.className = 'badge ' + (status === 'Đã duyệt' ? 'bg-success' : (status === 'Đã hủy' ? 'bg-secondary' : 'bg-warning'));
        new bootstrap.Modal(document.getElementById('modalDetail')).show();
    });
});
</script>
</body>
</html>