<?php 
session_start();
include 'db.php';

if(isset($_POST['btnBooking'])) {
    if(!isset($_SESSION['user_id'])) {
        echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login_user.php';</script>";
        exit();
    }

    $tour_id = $_POST['tour_id'];
    $user_id = $_SESSION['user_id'];
    $quantity = $_POST['quantity'];
    
    $cust_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $cust_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
    $dep_date = $_POST['departure_date'];
    
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    
    $sql_tour = "SELECT Price, TourName, MaxPeople FROM Tours WHERE TourID = $tour_id";
    $res_tour = mysqli_query($conn, $sql_tour);
    $row_tour = mysqli_fetch_assoc($res_tour);
    
   
    $limit = (isset($row_tour['MaxPeople']) && $row_tour['MaxPeople'] > 0) ? $row_tour['MaxPeople'] : 10;

   
    $sql_check = "SELECT SUM(Quantity) as TotalBooked FROM Bookings WHERE TourID = $tour_id AND Status != N'Đã hủy'";
    $res_check = mysqli_query($conn, $sql_check);
    $row_check = mysqli_fetch_assoc($res_check);
    $total_booked = $row_check['TotalBooked'] ? $row_check['TotalBooked'] : 0;

    
    if (($total_booked + $quantity) > $limit) {
        $slots_left = $limit - $total_booked;
        echo "<script>
                alert('Rất tiếc! Tour này chỉ còn trống $slots_left chỗ. Bạn không thể đặt $quantity người (Tối đa $limit người).');
                window.history.back();
              </script>";
        exit();
    }

    
    if($row_tour) {
        $total_price = $row_tour['Price'] * $quantity;
        $tour_name = $row_tour['TourName'];

        $sql_booking = "INSERT INTO Bookings (UserID, TourID, Quantity, TotalPrice, Status, BookingDate, DepartureDate, CustomerName, Phone, PaymentMethod) 
                        VALUES ('$user_id', '$tour_id', '$quantity', '$total_price', N'Chờ xác nhận', NOW(), '$dep_date', N'$cust_name', '$cust_phone', N'$payment_method')";

        if(mysqli_query($conn, $sql_booking)) {
            echo "<script>
                    alert('Đặt tour thành công!\\nPhương thức: $payment_method\\nTổng tiền: " . number_format($total_price) . "đ');
                    window.location.href = 'my_bookings.php';
                  </script>";
        } else {
            echo "Lỗi: " . mysqli_error($conn);
        }
    }
}
?>