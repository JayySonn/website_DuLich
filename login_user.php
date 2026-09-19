<?php
session_start();
include 'db.php';

if (isset($_POST['btnLogin'])) {
    $user = mysqli_real_escape_string($conn, $_POST['txtUser']);
    $pass = $_POST['txtPass'];

    $sql = "SELECT * FROM Users WHERE Username='$user' OR Email='$user'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($pass, $row['Password'])) {
            $_SESSION['user_id'] = $row['UserID'];
            $_SESSION['user_name'] = $row['FullName'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Mật khẩu không chính xác!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Lexend', sans-serif; background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: #fff; }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center fw-bold text-primary mb-4" style="color: #0d6efd !important;">ĐĂNG NHẬP</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger small'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Tên đăng nhập hoặc gmail</label>
                <input type="text" name="txtUser" class="form-control rounded-pill px-3 shadow-none bg-light border-0 py-2" required>
            </div>
            
            <div class="mb-2">
                <label class="form-label small fw-bold">Mật khẩu</label>
                <input type="password" name="txtPass" class="form-control rounded-pill px-3 shadow-none bg-light border-0 py-2" required>
            </div>
            
            <!-- Nút Quên mật khẩu được chèn vào đây -->
            <div class="text-end mb-4">
                <a href="forgot_password.php" class="text-decoration-none small fw-bold text-primary">Quên mật khẩu?</a>
            </div>

            <button type="submit" name="btnLogin" class="btn btn-primary w-100 rounded-pill fw-bold py-2" style="background-color: #0d6efd; border: none;">ĐĂNG NHẬP</button>
            <p class="text-center mt-3 small text-muted">Chưa có tài khoản? <a href="register.php" class="text-decoration-none fw-bold">Đăng ký ngay</a></p>
        </form>
    </div>
</body>
</html>