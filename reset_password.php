<?php
session_start();
include 'db.php';

$msg = "";
$valid_token = false;

// Kiểm tra token từ URL
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    
    // Tìm user có token này và chưa hết hạn
    $current_time = date("Y-m-d H:i:s");
    $sql = "SELECT * FROM Users WHERE reset_token = '$token' AND reset_expires >= '$current_time'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $valid_token = true;
        
        // Nếu submit mật khẩu mới
        if (isset($_POST['btnReset'])) {
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];
            
            if ($new_pass === $confirm_pass) {
                // Mã hóa mật khẩu (Lưu ý: Nếu trang Đăng ký của Sơn dùng MD5 thì để nguyên, nếu dùng password_hash thì đổi lại hàm)
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT); 
                
                // Cập nhật pass mới, đồng thời xóa token đi để không dùng lại được
                mysqli_query($conn, "UPDATE Users SET Password='$hashed_password', reset_token=NULL, reset_expires=NULL WHERE reset_token='$token'");
                
                $msg = "<div class='alert alert-success'>Đổi mật khẩu thành công! Đang chuyển hướng...</div>";
                echo "<script>setTimeout(function(){ window.location.href = 'login_user.php'; }, 3000);</script>";
                $valid_token = false; // Ẩn form đi
            } else {
                $msg = "<div class='alert alert-danger'>Mật khẩu xác nhận không khớp!</div>";
            }
        }
    } else {
        $msg = "<div class='alert alert-danger text-center mt-4'>Link không hợp lệ hoặc đã quá hạn 30 phút! <br><a href='forgot_password.php'>Gửi lại yêu cầu</a></div>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt Lại Mật Khẩu - Miền Tây Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Lexend', sans-serif; background-color: #f4f7f6; }</style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center mb-4">
            <h4 class="fw-bold mt-2" style="color: #082b5f;">Tạo Mật Khẩu Mới</h4>
        </div>
        <?php echo $msg; ?>
        
        <?php if($valid_token): ?>
        <form action="" method="POST">
            <div class="mb-3">
                <input type="password" name="new_password" class="form-control rounded-pill py-2 bg-light border-0" placeholder="Nhập mật khẩu mới..." required minlength="6">
            </div>
            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control rounded-pill py-2 bg-light border-0" placeholder="Xác nhận lại mật khẩu..." required minlength="6">
            </div>
            <button type="submit" name="btnReset" class="btn btn-success w-100 rounded-pill py-2 fw-bold">Xác nhận đổi</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>