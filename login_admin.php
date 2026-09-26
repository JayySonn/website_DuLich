<?php
session_start();
include 'db.php';

// Nếu đã đăng nhập Admin rồi thì đẩy thẳng vào Dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Truy vấn vào bảng admins
    $sql = "SELECT * FROM admins WHERE Username='$username'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $lock_until = $row['LockUntil'];
        
        // 1. Kiểm tra xem tài khoản Admin có đang bị khóa không
        if ($lock_until && strtotime($lock_until) > time()) {
            $remaining_minutes = ceil((strtotime($lock_until) - time()) / 60);
            $error = "Tài khoản Admin đang bị khóa do nhập sai quá 5 lần. Thử lại sau $remaining_minutes phút!";
        } else {
            // 2. Kiểm tra mật khẩu bằng password_verify
            if (password_verify($password, $row['Password'])) { 
                // Đăng nhập thành công -> Reset số lần đếm sai
                mysqli_query($conn, "UPDATE admins SET FailedAttempts = 0, LockUntil = NULL WHERE AdminID = " . $row['AdminID']);
                
                $_SESSION['admin_id'] = $row['AdminID'];
                $_SESSION['admin_user'] = $row['Username'];
                
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // Sai mật khẩu -> Tăng số lần đếm lỗi lên 1
                $attempts = $row['FailedAttempts'] + 1;
                
                if ($attempts >= 5) {
                    // Khóa 10 phút (600 giây)
                    $lock_time = date('Y-m-d H:i:s', time() + 600);
                    mysqli_query($conn, "UPDATE admins SET FailedAttempts = $attempts, LockUntil = '$lock_time' WHERE AdminID = " . $row['AdminID']);
                    $error = "Bạn đã nhập sai 5 lần liên tiếp. Tài khoản Admin đã bị khóa trong 10 phút!";
                } else {
                    mysqli_query($conn, "UPDATE admins SET FailedAttempts = $attempts WHERE AdminID = " . $row['AdminID']);
                    $remaining_turns = 5 - $attempts;
                    $error = "Sai mật khẩu quản trị! Bạn còn $remaining_turns lần thử trước khi bị khóa.";
                }
            }
        }
    } else {
        $error = "Tài khoản quản trị không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Miền Tây Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Thư viện Bootstrap Icons cho icon con mắt -->
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
            max-width: 420px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            font-weight: bold;
            padding: 10px;
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
        /* Căn chỉnh icon con mắt chìm trong ô mật khẩu */
        .password-container {
            position: relative;
        }
        .password-container .form-control {
            padding-right: 45px;
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
    <h3 class="text-center fw-bold text-primary mb-4">ADMIN LOGIN</h3>
    
    <?php if($error != ''): ?>
        <div class="alert alert-danger py-2 small text-center fw-bold"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label small fw-bold">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Mật khẩu</label>
            <div class="password-container">
                <input type="password" name="password" id="passwordInput" class="form-control" required>
                <i class="bi bi-eye-slash" id="togglePassword" title="Hiện/Ẩn mật khẩu"></i>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 rounded-pill mb-3">ĐĂNG NHẬP</button>
        
        <div class="text-center small">
            <a href="index.php" class="text-muted text-decoration-none">&larr; Quay về trang chủ</a>
        </div>
    </form>
</div>

<!-- Script xử lý ẩn/hiện mật khẩu -->
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#passwordInput');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>