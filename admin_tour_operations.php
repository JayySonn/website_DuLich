<?php
session_start();
include 'db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Điều hành Tour - Miền Tây Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .accordion-button:not(.collapsed) {
            background-color: #e7f1ff;
            color: #0c63e4;
            font-weight: bold;
        }
        .tour-header-info {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding-right: 15px;
            align-items: center;
        }
        /* CSS cho ảnh đại diện Tour */
        .tour-thumbnail {
            width: 70px;
            height: 45px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .tour-title-wrap {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="bi bi-bus-front me-2 text-primary"></i> ĐIỀU HÀNH ĐOÀN THEO TOUR</h3>
    </div>

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <div class="accordion" id="tourAccordion">

            <?php
            // Đã thêm WHERE b.DepartureDate >= CURDATE() để ẩn tour cũ
            $sql_groups = "
                SELECT 
                    b.TourID, 
                    t.TourName, 
                    t.ImageURL, 
                    b.DepartureDate, 
                    COUNT(b.BookingID) as TongSoDon, 
                    SUM(b.Quantity) as TongSoKhach 
                FROM bookings b
                JOIN tours t ON b.TourID = t.TourID
                WHERE b.DepartureDate >= CURDATE() 
                GROUP BY b.TourID, t.TourName, t.ImageURL, b.DepartureDate 
                ORDER BY b.DepartureDate ASC
            ";
            
            $result_groups = mysqli_query($conn, $sql_groups);
            $index = 0;

            if ($result_groups && mysqli_num_rows($result_groups) > 0) {
                while ($group = mysqli_fetch_assoc($result_groups)) {
                    $index++;
                    $tourID = $group['TourID'];
                    $tourName = $group['TourName'];
                    $tourImage = $group['ImageURL'];
                    $ngayDi = $group['DepartureDate'];
                    $tongDon = $group['TongSoDon'];
                    $tongKhach = $group['TongSoKhach'];
                    $collapseId = "collapse_" . $index;
            ?>
                    <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>">
                                <div class="tour-header-info">
                                    <div class="tour-title-wrap">
                                        <?php if (!empty($tourImage)): ?>
                                            <img src="<?php echo $tourImage; ?>" alt="Tour Image" class="tour-thumbnail">
                                        <?php else: ?>
                                            <img src="images/default-tour.jpg" alt="Default" class="tour-thumbnail">
                                        <?php endif; ?>
                                        <span><strong><?php echo $tourName; ?></strong></span>
                                    </div>

                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-calendar-event me-1"></i> Khởi hành: <strong class="me-3"><?php echo date('d/m/Y', strtotime($ngayDi)); ?></strong>
                                        <span class="badge bg-secondary rounded-pill me-1"><?php echo $tongDon; ?> Đơn</span>
                                        <span class="badge bg-success rounded-pill">Tổng: <?php echo $tongKhach; ?> Khách</span>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        
                        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse" data-bs-parent="#tourAccordion">
                            <div class="accordion-body bg-light p-3">
                                <table class="table table-hover table-bordered bg-white mb-0 align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th width="10%">Mã Đơn</th>
                                            <th width="35%">Tên Khách Hàng</th>
                                            <th width="20%">Số Điện Thoại</th>
                                            <th width="15%">Số lượng</th>
                                            <th width="20%">Trạng Thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_details = "SELECT * FROM bookings WHERE TourID = '$tourID' AND DepartureDate = '$ngayDi' ORDER BY BookingID DESC";
                                        $result_details = mysqli_query($conn, $sql_details);

                                        while ($detail = mysqli_fetch_assoc($result_details)) {
                                            $badgeClass = ($detail['Status'] == 'Chờ xác nhận') ? 'bg-warning text-dark' : 'bg-primary';
                                        ?>
                                            <tr>
                                                <td class="text-center fw-medium text-secondary">#<?php echo $detail['BookingID']; ?></td>
                                                <td class="fw-bold"><?php echo $detail['CustomerName']; ?></td>
                                                <td class="text-center"><i class="bi bi-telephone-fill text-muted me-1 small"></i> <?php echo $detail['Phone']; ?></td>
                                                <td class="text-center fw-bold text-danger fs-5"><?php echo $detail['Quantity']; ?></td>
                                                <td class="text-center"><span class="badge <?php echo $badgeClass; ?> px-3 py-2"><?php echo $detail['Status']; ?></span></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo '<div class="alert alert-info text-center"><i class="bi bi-info-circle me-2"></i> Hiện tại không có đoàn tour nào sắp khởi hành.</div>';
            }
            ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>