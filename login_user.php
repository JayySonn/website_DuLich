<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE Username='$username_or_email' OR Email='$username_or_email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $lock_until = $row['LockUntil'];
        
        if ($lock_until && strtotime($lock_until) > time()) {
            $remaining_minutes = ceil((strtotime($lock_until) - time()) / 60);
            $error = "Tài khoản đang bị khóa do nhập sai quá 5 lần. Vui lòng thử lại sau $remaining_minutes phút!";
        } else {
            if (password_verify($password, $row['Password'])) {
                mysqli_query($conn, "UPDATE Users SET FailedAttempts = 0, LockUntil = NULL WHERE UserID = " . $row['UserID']);
                
                $_SESSION['user'] = $row['Username'];
                $_SESSION['user_id'] = $row['UserID'];
                header("Location: index.php");
                exit();
            } else {
                $attempts = $row['FailedAttempts'] + 1;
                
                if ($attempts >= 5) {
                    $lock_time = date('Y-m-d H:i:s', time() + 600);
                    mysqli_query($conn, "UPDATE Users SET FailedAttempts = $attempts, LockUntil = '$lock_time' WHERE UserID = " . $row['UserID']);
                    $error = "Bạn đã nhập sai 5 lần liên tiếp. Tài khoản đã bị khóa trong 10 phút!";
                } else {
                    mysqli_query($conn, "UPDATE Users SET FailedAttempts = $attempts WHERE UserID = " . $row['UserID']);
                    $remaining_turns = 5 - $attempts;
                    $error = "Mật khẩu không chính xác! Bạn còn $remaining_turns lần thử trước khi bị khóa.";
                }
            }
        }
    } else {
        $error = "Tên đăng nhập hoặc Email không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - Miền Tây Travel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (Thư viện icon con mắt chuẩn) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Lexend', sans-serif;
        }
        .login-card {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 450px;
        }
        .text-primary-custom {
            color: #0d6efd !important;
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
        .form-control {
            background-color: #f8f9fa;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 50rem !important;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            background-color: #fff;
        }
        /* Tinh chỉnh vị trí icon con mắt chìm trong ô input */
        .password-container {
            position: relative;
        }
        .password-container .form-control {
            padding-right: 45px; /* Tạo khoảng trống bên phải để chữ không bị đè lên icon */
        }
        #togglePassword {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 1.1rem;
            z-index: 10;
        }
        #togglePassword:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center fw-bold text-primary-custom mb-4 text-uppercase">ĐĂNG NHẬP</h3>
    
    <?php if($error != ''): ?>
        <div class="alert alert-danger py-2 small text-center fw-bold"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold">Tên đăng nhập hoặc gmail</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Mật khẩu</label>
            <!-- Khung chứa ô input và icon con mắt chìm bên trong -->
            <div class="password-container">
                <input type="password" name="password" id="passwordInput" class="form-control" required>
                <i class="bi bi-eye-slash" id="togglePassword" title="Hiện/Ẩn mật khẩu"></i>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 small">
            <div></div>
            <a href="forgot_password.php" class="text-primary-custom text-decoration-none">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn btn-primary-custom w-100 rounded-pill mb-3 text-white">ĐĂNG NHẬP</button>
        
        <div class="text-center small">
            Chưa có tài khoản? <a href="register.php" class="text-primary-custom text-decoration-none fw-bold">Đăng ký ngay</a>
        </div>
    </form>
</div>

<!-- Script xử lý ẩn/hiện icon con mắt -->
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#passwordInput');

    togglePassword.addEventListener('click', function () {
        // Đổi loại input giữa password và text
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Đổi icon con mắt mở/đóng tương ứng của Bootstrap Icons
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>