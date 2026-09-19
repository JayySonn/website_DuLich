<?php

require_once dirname(__DIR__) . '/libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include 'db.php';
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    
    $sql = "SELECT Bookings.*, Tours.TourName, Tours.DepartureLocation 
            FROM Bookings 
            JOIN Tours ON Bookings.TourID = Tours.TourID 
            WHERE BookingID = $id";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);

    if ($row) {
        $options = new Options();
        $options->set('isRemoteEnabled', true); 
        $dompdf = new Dompdf($options);

       
        $html = '
        <style>
            body { font-family: "DejaVu Sans", sans-serif; font-size: 14px; }
            .header { text-align: center; margin-bottom: 30px; }
            .invoice-title { font-size: 22px; font-weight: bold; color: #0d6efd; }
            .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
            .info-table td { padding: 8px; border-bottom: 1px solid #eee; }
            .total-row { font-size: 18px; font-weight: bold; color: #dc3545; text-align: right; }
            .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #777; }
        </style>
        <div class="header">
            <div class="invoice-title">MIỀNTÂY TRAVEL</div>
            <div>Uy tín - Chất lượng - Trải nghiệm</div>
            <hr>
            <h3>HÓA ĐƠN XÁC NHẬN ĐẶT TOUR</h3>
            <p>Mã đơn hàng: #'.$id.'</p>
        </div>

        <table class="info-table">
            <tr><td><b>Khách hàng:</b></td><td>'.$row['CustomerName'].'</td></tr>
            <tr><td><b>Điện thoại:</b></td><td>'.$row['Phone'].'</td></tr>
            <tr><td><b>Tên Tour:</b></td><td>'.$row['TourName'].'</td></tr>
            <tr><td><b>Khởi hành từ:</b></td><td>'.$row['DepartureLocation'].'</td></tr>
            <tr><td><b>Ngày đi:</b></td><td>'.date('d/m/Y', strtotime($row['DepartureDate'])).'</td></tr>
            <tr><td><b>Số lượng khách:</b></td><td>'.$row['Quantity'].' người</td></tr>
            <tr><td><b>Thanh toán qua:</b></td><td>'.$row['PaymentMethod'].'</td></tr>
        </table>

        <div class="total-row">TỔNG CỘNG: '.number_format($row['TotalPrice']).'đ</div>

        <div class="footer">
            <p>Hóa đơn này có giá trị xác nhận quý khách đã giữ chỗ thành công.</p>
            <p>Mọi thắc mắc vui lòng liên hệ: 0123 456 789</p>
            <p><i>Ngày xuất hóa đơn: '.date('d/m/Y H:i').'</i></p>
        </div>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait'); 
        $dompdf->render();
        $dompdf->stream("HoaDon_MienTayTravel_".$id.".pdf", array("Attachment" => 1));
    }
}