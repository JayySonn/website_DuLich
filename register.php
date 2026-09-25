<?php
session_start();
include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Kiểm tra xem Username hoặc Email đã tồn tại chưa
    $sql_check = "SELECT * FROM Users WHERE Username='$username' OR Email='$email'";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        $error = "Tên đăng nhập hoặc Email đã được sử dụng!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // THÊM VÀO CƠ SỞ DỮ LIỆU (Đã xóa cột 'Role' gây lỗi)
        $sql_insert = "INSERT INTO Users (FullName, Email, Username, Password) 
                       VALUES ('$fullname', '$email', '$username', '$hashed_password')";

        if (mysqli_query($conn, $sql_insert)) {
            $success = "Đăng ký thành công! Vui lòng đăng nhập.";
        } else {
            $error = "Lỗi khi đăng ký: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký - Miền Tây Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Sử dụng phông chữ giống login_user.php -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6; /* Nền giống login_user.php */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Lexend', sans-serif; /* Phông chữ đồng bộ */
        }
        .register-card {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); /* Bóng đổ giống login_user.php */
            width: 100%;
            max-width: 450px;
        }
        /* Thay đổi màu sắc thành xanh dương giống login_user.php */
        .text-primary-custom {
            color: #0d6efd !important; /* Xanh dương Bootstrap mặc định */
        }
        .btn-primary-custom {
            background-color: #0d6efd;
            border: none;
            font-weight: bold;
            padding: 10px;
        }
        .btn-primary-custom:hover {
            background-color: #0b5ed7;
        }
        /* Style cho input giống login_user.php */
        .form-control {
            background-color: #f8f9fa;
            border: none;
            border-radius: 50rem !important; /* Bo tròn giống login_user.php */
            padding: 0.75rem 1.25rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            background-color: #fff;
        }
    </style>
</head>
<body>

<div class="register-card">
    <h3 class="text-center fw-bold text-primary-custom mb-4 text-uppercase">ĐĂNG KÝ</h3>
    
    <?php if($error != ''): ?>
        <div class="alert alert-danger py-2 small text-center fw-bold"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success != ''): ?>
        <div class="alert alert-success py-2 small text-center fw-bold"><?php echo $success; ?></div>
        <!-- Chuyển hướng về trang đăng nhập sau 2 giây nếu thành công -->
        <script>
            setTimeout(function(){
                window.location.href = 'login_user.php';
            }, 2000);
        </script>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold">Họ và tên</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 rounded-pill mb-3 text-white">ĐĂNG KÝ</button>
        
        <div class="text-center small">
            Đã có tài khoản? <a href="login_user.php" class="text-primary-custom text-decoration-none fw-bold">Đăng nhập ngay</a>
        </div>
    </form>
</div>

</body>
</html>