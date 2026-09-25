<?php 
session_start();
include 'db.php';

if(isset($_POST['btnBooking'])) {
    if(!isset($_SESSION['user_id'])) {
        echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login_user.php';</script>";
        exit();
    }

    // VÁ LỖ HỔNG 4: Bắt buộc dùng intval() cho các dữ liệu số
    $tour_id = intval($_POST['tour_id']);
    $user_id = intval($_SESSION['user_id']);
    $quantity = intval($_POST['quantity']);
    
    // VÁ LỖ HỔNG 2: Chống Hack số lượng âm (VD: nhập -5 người để làm âm tổng tiền)
    if ($quantity <= 0) {
        echo "<script>alert('Lỗi: Số lượng người đặt không hợp lệ!'); window.history.back();</script>";
        exit();
    }

    $cust_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $cust_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $dep_date = mysqli_real_escape_string($conn, $_POST['departure_date']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // KIỂM TRA SỐ ĐIỆN THOẠI BẰNG SERVER (Chống Hacker bypass HTML)
    // Phải đúng 10 số và thuộc các đầu số VN (03, 05, 07, 08, 09)
    if (!preg_match('/^(03|05|07|08|09)[0-9]{8}$/', $cust_phone)) {
        echo "<script>alert('Lỗi: Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 số của các nhà mạng Việt Nam.'); window.history.back();</script>";
        exit();
    }
    
    // KIỂM TRA NGÀY KHỞI HÀNH (Chống đặt tour đã qua hạn)
    $today = date('Y-m-d');
    if (strtotime($dep_date) < strtotime($today)) {
        echo "<script>alert('Lỗi: Ngày khởi hành này đã qua. Hệ thống từ chối nhận đơn!'); window.history.back();</script>";
        exit();
    }
    
    // Hệ thống tự động lấy giá gốc trong CSDL để tính (Chống Hack Giá)
    $sql_tour = "SELECT Price, TourName, MaxPeople FROM Tours WHERE TourID = $tour_id";
    $res_tour = mysqli_query($conn, $sql_tour);
    $row_tour = mysqli_fetch_assoc($res_tour);
    
    if(!$row_tour) {
        echo "<script>alert('Lỗi: Không tìm thấy Tour!'); window.history.back();</script>";
        exit();
    }
    
    $limit = (isset($row_tour['MaxPeople']) && $row_tour['MaxPeople'] > 0) ? $row_tour['MaxPeople'] : 10;

    $sql_check = "SELECT SUM(Quantity) as TotalBooked FROM Bookings WHERE TourID = $tour_id AND Status != N'Đã hủy'";
    $res_check = mysqli_query($conn, $sql_check);
    $row_check = mysqli_fetch_assoc($res_check);
    $total_booked = $row_check['TotalBooked'] ? intval($row_check['TotalBooked']) : 0;

    if (($total_booked + $quantity) > $limit) {
        $slots_left = $limit - $total_booked;
        echo "<script>
                alert('Rất tiếc! Tour này chỉ còn trống $slots_left chỗ. Bạn không thể đặt $quantity người (Tối đa $limit người).');
                window.history.back();
              </script>";
        exit();
    }

    // TÍNH TỔNG TIỀN AN TOÀN 100%
    $total_price = $row_tour['Price'] * $quantity;
    $tour_name = $row_tour['TourName'];

    $sql_booking = "INSERT INTO Bookings (UserID, TourID, Quantity, TotalPrice, Status, BookingDate, DepartureDate, CustomerName, Phone, PaymentMethod) 
                    VALUES ($user_id, $tour_id, $quantity, $total_price, N'Chờ xác nhận', NOW(), '$dep_date', N'$cust_name', '$cust_phone', N'$payment_method')";

    if(mysqli_query($conn, $sql_booking)) {
        echo "<script>
                alert('Đặt tour thành công!\\nPhương thức: $payment_method\\nTổng tiền: " . number_format($total_price) . "đ');
                window.location.href = 'my_bookings.php';
              </script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>