<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// --- 1. TRUY VẤN DỮ LIỆU THỐNG KÊ TỔNG QUAN ---

// Tổng doanh thu (Chỉ tính những đơn 'Đã duyệt')
$sql_revenue = "SELECT SUM(TotalPrice) AS Revenue FROM Bookings WHERE Status = 'Đã duyệt'";
$res_revenue = mysqli_query($conn, $sql_revenue);
$total_revenue = mysqli_fetch_assoc($res_revenue)['Revenue'] ?? 0;

// Tổng số đơn hàng
$sql_orders = "SELECT COUNT(BookingID) AS TotalOrders FROM Bookings";
$res_orders = mysqli_query($conn, $sql_orders);
$total_orders = mysqli_fetch_assoc($res_orders)['TotalOrders'] ?? 0;

// Số đơn đang chờ xác nhận
$sql_pending = "SELECT COUNT(BookingID) AS PendingOrders FROM Bookings WHERE Status = 'Chờ xác nhận'";
$res_pending = mysqli_query($conn, $sql_pending);
$pending_orders = mysqli_fetch_assoc($res_pending)['PendingOrders'] ?? 0;

// Tổng số Tour đang có trên hệ thống
$sql_tours = "SELECT COUNT(TourID) AS TotalTours FROM Tours";
$res_tours = mysqli_query($conn, $sql_tours);
$total_tours = mysqli_fetch_assoc($res_tours)['TotalTours'] ?? 0;


// --- 2. DỮ LIỆU CHO BIỂU ĐỒ TRÒN (Thống kê Trạng thái Đơn) ---
$sql_chart_status = "SELECT Status, COUNT(BookingID) as Count FROM Bookings GROUP BY Status";
$res_chart_status = mysqli_query($conn, $sql_chart_status);

$status_labels = [];
$status_data = [];
$status_colors = [];

while ($row = mysqli_fetch_assoc($res_chart_status)) {
    $status_labels[] = $row['Status'];
    $status_data[] = $row['Count'];
    
    // Set màu cho từng trạng thái để biểu đồ đẹp hơn
    if ($row['Status'] == 'Đã duyệt') $status_colors[] = '#198754'; // Xanh lá
    elseif ($row['Status'] == 'Chờ xác nhận') $status_colors[] = '#ffc107'; // Vàng
    else $status_colors[] = '#dc3545'; // Đỏ (Đã hủy)
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê Tổng quan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Nạp thư viện Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .stat-card { border: none; border-radius: 15px; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-box { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 24px; }
        .chart-container { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mt-4 mb-5">
    <h2 class="fw-bold mb-4 text-dark"><i class="bi bi-speedometer2 text-primary me-2"></i> Dashboard Thống Kê</h2>

    <!-- HÀNG 1: 4 THẺ TÓM TẮT -->
    <div class="row g-4 mb-4">
        <!-- Thẻ Doanh thu -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Doanh Thu Thực Tế</h6>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo number_format($total_revenue); ?>đ</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thẻ Tổng Đơn -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Tổng Số Đơn Đặt</h6>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $total_orders; ?> <span class="fs-6 fw-normal text-muted">đơn</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thẻ Chờ Duyệt -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Đơn Chờ Xử Lý</h6>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $pending_orders; ?> <span class="fs-6 fw-normal text-muted">đơn</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thẻ Số Tour -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Tour Đang Mở Bán</h6>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $total_tours; ?> <span class="fs-6 fw-normal text-muted">tour</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HÀNG 2: BIỂU ĐỒ -->
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="chart-container h-100">
                <h5 class="fw-bold mb-4 text-center">Tỷ Lệ Trạng Thái Đơn Hàng</h5>
                <!-- Khung vẽ biểu đồ tròn -->
                <div style="position: relative; height:300px; width:100%; display: flex; justify-content: center;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-container h-100 d-flex flex-column justify-content-center align-items-center text-center">
                <i class="bi bi-rocket-takeoff text-primary opacity-50" style="font-size: 5rem;"></i>
                <h4 class="fw-bold mt-3">Hệ thống đang hoạt động ổn định</h4>
                <p class="text-muted">Bạn có <b><?php echo $pending_orders; ?></b> đơn hàng mới cần được duyệt ngay hôm nay.</p>
                <a href="admin_bookings.php" class="btn btn-primary rounded-pill px-4 mt-2">Đến trang Quản lý đơn</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Nhận dữ liệu từ PHP chuyển sang định dạng JSON cho Javascript
    const statusLabels = <?php echo json_encode($status_labels); ?>;
    const statusData = <?php echo json_encode($status_data); ?>;
    const statusColors = <?php echo json_encode($status_colors); ?>;

    // Cấu hình vẽ biểu đồ Doughnut (Hình khuyên)
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                borderWidth: 2,
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>