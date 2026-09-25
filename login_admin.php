<?php
session_start();
include 'db.php';

// Nếu đã đăng nhập Admin rồi thì đẩy thẳng vào Dashboard, không cần đăng nhập lại
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // TRUY VẤN VÀO BẢNG admins
    $sql = "SELECT * FROM admins WHERE Username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // CẬP NHẬT BẢO MẬT: Kiểm tra mật khẩu bằng hàm password_verify
        if (password_verify($password, $row['Password'])) { 
            
            // Cấp thẻ bài Admin
            $_SESSION['admin_id'] = $row['AdminID'];
            $_SESSION['admin_user'] = $row['Username'];
            
            echo "<script>alert('Xin chào Quản trị viên!'); window.location.href='admin_dashboard.php';</script>";
        } else {
            $error = "Sai mật khẩu quản trị!";
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
    <style>
        body {
            background-color: #f4f7f6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-card {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            font-weight: bold;
            padding: 10px;
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
            <input type="text" name="username" class="form-control rounded-pill px-3" required>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Mật khẩu</label>
            <input type="password" name="password" class="form-control rounded-pill px-3" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 rounded-pill mb-3">ĐĂNG NHẬP</button>
        
        <div class="text-center small">
            <a href="index.php" class="text-muted text-decoration-none">&larr; Quay về trang chủ</a>
        </div>
    </form>
</div>

</body>
</html>