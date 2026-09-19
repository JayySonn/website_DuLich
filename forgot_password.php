<?php
session_start();
include 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Yêu cầu nạp thư viện PHPMailer
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$msg = "";

if (isset($_POST['btnForgot'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Kiểm tra email có trong DB không
    $check_email = mysqli_query($conn, "SELECT * FROM Users WHERE Email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $user = mysqli_fetch_assoc($check_email);
        
        // Tạo token ngẫu nhiên và thời gian hết hạn (30 phút)
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", time() + 1800);
        
        // Cập nhật token vào DB
        mysqli_query($conn, "UPDATE Users SET reset_token='$token', reset_expires='$expires' WHERE Email='$email'");
        
        // Link khôi phục mật khẩu (Sơn sửa lại tên thư mục web_du_lich cho đúng với máy bạn nhé)
        $reset_link = "http://localhost/web_du_lich/reset_password.php?token=" . $token;

        // CẤU HÌNH GỬI MAIL
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jayson18088@gmail.com'; // ĐIỀN EMAIL CỦA SƠN VÀO ĐÂY
            $mail->Password   = 'ogbyrnakmyoyqtmb'; // ĐIỀN MẬT KHẨU ỨNG DỤNG LẤY Ở BƯỚC 2
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('email_cua_ban@gmail.com', 'Miền Tây Travel');
            $mail->addAddress($email, $user['FullName']);

            $mail->isHTML(true);
            $mail->Subject = 'Yêu cầu khôi phục mật khẩu - Miền Tây Travel';
            $mail->Body    = "Chào <b>{$user['FullName']}</b>,<br><br>Chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn.<br>Vui lòng click vào đường link dưới đây để đặt lại mật khẩu (Link có hiệu lực trong 30 phút):<br><br><a href='$reset_link' style='padding:10px 20px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:5px;'>ĐẶT LẠI MẬT KHẨU</a><br><br>Nếu bạn không yêu cầu, vui lòng bỏ qua email này.";

            $mail->send();
            $msg = "<div class='alert alert-success'>Link khôi phục đã được gửi vào Email của bạn. Vui lòng kiểm tra hộp thư!</div>";
        } catch (Exception $e) {
            $msg = "<div class='alert alert-danger'>Không thể gửi email. Lỗi: {$mail->ErrorInfo}</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger'>Email này chưa được đăng ký trong hệ thống!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên Mật Khẩu - Miền Tây Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Lexend', sans-serif; background-color: #f4f7f6; }</style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <img src="https://cdn-icons-png.flaticon.com/128/5968/5968879.png" width="60" alt="Logo">
            <h4 class="fw-bold mt-2" style="color: #082b5f;">Quên Mật Khẩu</h4>
            <p class="text-muted small">Nhập email của bạn để nhận link đặt lại</p>
        </div>
        <?php echo $msg; ?>
        <form action="" method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control rounded-pill py-2 bg-light border-0" placeholder="Nhập Email đã đăng ký..." required>
            </div>
            <button type="submit" name="btnForgot" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="background-color: #082b5f;">Gửi Yêu Cầu</button>
        </form>
        <div class="text-center mt-3">
            <a href="login_user.php" class="text-decoration-none small text-muted">Quay lại Đăng nhập</a>
        </div>
    </div>
</body>
</html>