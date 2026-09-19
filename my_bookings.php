<?php
session_start();
include 'db.php';

// Kiểm tra nếu chưa đăng nhập thì bắt buộc về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 1. TẠO CSRF TOKEN BẢO MẬT ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- 2. XỬ LÝ KHÁCH HÀNG YÊU CẦU HỦY ĐƠN TỰ ĐỘNG ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    // Kiểm tra token bảo mật
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật!'); window.location.href='my_bookings.php';</script>");
    }

    $booking_id = intval($_POST['booking_id']);
    
    // Nâng cấp: Chỉ cho phép hủy những đơn của chính user này và đang ở trạng thái 'Chờ xác nhận'
    $sql_cancel = "UPDATE bookings SET Status = 'Đã hủy' WHERE BookingID = ? AND UserID = ? AND Status = 'Chờ xác nhận'";
    $stmt_cancel = mysqli_prepare($conn, $sql_cancel);
    mysqli_stmt_bind_param($stmt_cancel, "ii", $booking_id, $user_id);
    
    if (mysqli_stmt_execute($stmt_cancel)) {
        header("Location: my_bookings.php?msg=cancelled");
        exit();
    }
}

// --- 3. LẤY DANH SÁCH ĐƠN HÀNG CỦA KHÁCH NÀY ---
$sql_bookings = "SELECT b.*, t.TourName, t.ImageURL 
                 FROM bookings b 
                 JOIN tours t ON b.TourID = t.TourID 
                 WHERE b.UserID = ? 
                 ORDER BY b.BookingDate DESC";
$stmt = mysqli_prepare($conn, $sql_bookings);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đặt tour - MIỀNTÂY TRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Lexend', sans-serif; background-color: #f4f7f9; }
        .page-header { background: #0d6efd; padding: 60px 0; color: white; text-align: center; }
        .booking-card { border: none; border-radius: 15px; overflow: hidden; transition: 0.3s; }
        .booking-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
        .tour-img { width: 100%; height: 180px; object-fit: cover; border-radius: 15px; }
        .status-badge { padding: 8px 15px; border-radius: 30px; font-weight: 600; font-size: 0.85rem; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="page-header shadow-sm">
        <div class="container">
            <h1 class="fw-bold display-5 mb-2">Lịch Sử Đặt Tour</h1>
            <p class="lead opacity-75 mb-0">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Đây là danh sách các chuyến đi của bạn.</p>
        </div>
    </div>

    <div class="container mt-5 mb-5" style="min-height: 50vh;">
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'cancelled'): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Hủy chuyến đi thành công. Hẹn gặp lại bạn ở những hành trình sau!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row g-4">
                <?php while ($row = mysqli_fetch_assoc($result)): 
                    // Set màu sắc cho trạng thái
                    $status_class = 'bg-warning text-dark';
                    $status_icon = 'bi-hourglass-split';
                    if ($row['Status'] == 'Đã duyệt') {
                        $status_class = 'bg-success text-white';
                        $status_icon = 'bi-check-circle';
                    } elseif ($row['Status'] == 'Đã hủy') {
                        $status_class = 'bg-secondary text-white';
                        $status_icon = 'bi-x-circle';
                    }
                ?>
                    <div class="col-12">
                        <div class="card booking-card shadow-sm p-3">
                            <div class="row g-3 align-items-center">
                                <!-- Ảnh Tour -->
                                <div class="col-md-3 col-sm-4">
                                    <img src="<?php echo htmlspecialchars($row['ImageURL']); ?>" class="tour-img" alt="Tour" onerror="this.src='https://placehold.co/400x300?text=No+Image';">
                                </div>
                                
                                <!-- Thông tin Tour -->
                                <div class="col-md-6 col-sm-8">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['TourName']); ?></h5>
                                    </div>
                                    
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-upc-scan me-1"></i> Mã đơn: <strong class="text-dark">#<?php echo $row['BookingID']; ?></strong> | 
                                        <i class="bi bi-calendar-event me-1"></i> Đặt ngày: <?php echo date('d/m/Y', strtotime($row['BookingDate'])); ?>
                                    </p>
                                    
                                    <div class="row text-dark small bg-light p-3 rounded-3 mt-3 g-2">
                                        <div class="col-6">
                                            <i class="bi bi-people text-primary me-1"></i> Số lượng: <strong><?php echo $row['Quantity']; ?> khách</strong>
                                        </div>
                                        <div class="col-6">
                                            <i class="bi bi-calendar2-check text-primary me-1"></i> Ngày đi: <strong><?php echo date('d/m/Y', strtotime($row['DepartureDate'])); ?></strong>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <i class="bi bi-cash-stack text-primary me-1"></i> Tổng tiền: <strong class="text-danger fs-6"><?php echo number_format($row['TotalPrice']); ?>đ</strong>
                                            <span class="text-muted ms-2">(Thanh toán: <?php echo htmlspecialchars($row['PaymentMethod']); ?>)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Trạng thái & Các nút thao tác -->
                                <div class="col-md-3 text-md-end text-start mt-3 mt-md-0 d-flex flex-md-column justify-content-between h-100">
                                    <div class="mb-3">
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="bi <?php echo $status_icon; ?> me-1"></i> <?php echo htmlspecialchars($row['Status']); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($row['Status'] == 'Chờ xác nhận'): ?>
                                        <!-- Nút Hủy đơn khi đang chờ duyệt -->
                                        <form method="POST" action="my_bookings.php" class="mb-0">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['BookingID']; ?>">
                                            <button type="submit" name="cancel_booking" class="btn btn-outline-danger rounded-pill px-4 w-100" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn đặt tour này không?');">
                                                Hủy đặt tour
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['Status'] == 'Đã duyệt'): ?>
                                        <!-- Các nút chức năng khi đơn đã được duyệt -->
                                        <div class="d-flex flex-column gap-2">
                                            <!-- Nút xuất PDF -->
                                            <a href="export_pdf.php?id=<?php echo $row['BookingID']; ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-4 shadow-sm w-100" title="Tải vé / Hóa đơn điện tử">
                                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Tải vé PDF
                                            </a>
                                            
                                            <!-- Nút viết đánh giá -->
                                            <a href="detail.php?id=<?php echo $row['TourID']; ?>#review-section" class="btn btn-outline-primary rounded-pill px-4 shadow-sm w-100">
                                                <i class="bi bi-pencil-square me-1"></i> Viết đánh giá
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- Trạng thái trống -->
            <div class="text-center py-5 mt-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 5rem;"></i>
                <h3 class="fw-bold mt-3 text-dark">Bạn chưa đặt chuyến đi nào</h3>
                <p class="text-muted">Hàng ngàn cảnh đẹp miền Tây đang chờ bạn khám phá. Bắt đầu hành trình ngay!</p>
                <a href="index.php" class="btn btn-primary rounded-pill px-5 py-2 mt-2 fw-bold shadow-sm">Khám phá Tour ngay</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 small text-white-50">© 2026 MiềnTây Travel - Đồng hành cùng bạn trên mọi nẻo đường.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>