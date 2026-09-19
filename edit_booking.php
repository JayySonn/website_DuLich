<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])) { header("Location: login_user.php"); exit(); }

$user_id = $_SESSION['user_id'];


if(isset($_GET['id'])) {
    $booking_id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT Bookings.*, Tours.TourName FROM Bookings 
            JOIN Tours ON Bookings.TourID = Tours.TourID 
            WHERE BookingID = $booking_id AND UserID = $user_id AND Status = N'Chờ xác nhận'";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);

    if(!$data) {
        echo "<script>alert('Không tìm thấy đơn hàng hoặc đơn hàng đã được duyệt!'); window.location.href='my_bookings.php';</script>";
        exit();
    }
}


if(isset($_POST['btnUpdate'])) {
    $name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $date = $_POST['departure_date'];
    $qty = $_POST['quantity'];
    
    
    $sql_price = "SELECT Price FROM Tours WHERE TourID = " . $data['TourID'];
    $res_price = mysqli_query($conn, $sql_price);
    $row_price = mysqli_fetch_assoc($res_price);
    $total_price = $row_price['Price'] * $qty;

    $sql_update = "UPDATE Bookings SET 
                   CustomerName = N'$name', 
                   Phone = '$phone', 
                   DepartureDate = '$date', 
                   Quantity = '$qty',
                   TotalPrice = '$total_price'
                   WHERE BookingID = $booking_id AND UserID = $user_id";

    if(mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Cập nhật đơn hàng thành công!'); window.location.href='my_bookings.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa đơn hàng - MIỀNTÂY TRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Lexend', sans-serif; }
        .edit-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card edit-card p-4">
                    <h3 class="fw-bold mb-4 text-primary text-center">Sửa thông tin đặt tour</h3>
                    <p class="text-center text-muted">Tour: <strong><?php echo $data['TourName']; ?></strong></p>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Họ và tên người đặt:</label>
                            <input type="text" name="customer_name" class="form-control" value="<?php echo $data['CustomerName']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Số điện thoại:</label>
                            <input type="text" name="customer_phone" class="form-control" value="<?php echo $data['Phone']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ngày khởi hành dự kiến:</label>
                            <input type="date" name="departure_date" class="form-control" value="<?php echo $data['DepartureDate']; ?>" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Số người tham gia:</label>
                            <input type="number" name="quantity" class="form-control" value="<?php echo $data['Quantity']; ?>" min="1" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="btnUpdate" class="btn btn-primary rounded-pill fw-bold py-2">LƯU THAY ĐỔI</button>
                            <a href="my_bookings.php" class="btn btn-light rounded-pill py-2">Hủy bỏ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>