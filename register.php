<?php
include 'db.php';

if (isset($_POST['btnRegister'])) {
    $user = mysqli_real_escape_string($conn, $_POST['txtUser']);
    $pass = password_hash($_POST['txtPass'], PASSWORD_DEFAULT); 
    $name = mysqli_real_escape_string($conn, $_POST['txtName']);
    $email = mysqli_real_escape_string($conn, $_POST['txtEmail']);

    
    $check = mysqli_query($conn, "SELECT * FROM Users WHERE Username='$user'");
    if(mysqli_num_rows($check) > 0) {
        $error = "Tên đăng nhập này đã có người sử dụng!";
    } else {
        $sql = "INSERT INTO Users (Username, Password, FullName, Email) VALUES ('$user', '$pass', '$name', '$email')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Đăng ký thành công! Hãy đăng nhập.'); window.location='login_user.php';</script>";
        } else {
            $error = "Lỗi đăng ký: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reg-card { width: 100%; max-width: 450px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: #fff; }
    </style>
</head>
<body>
    <div class="reg-card">
        <h3 class="text-center fw-bold text-success mb-4">ĐĂNG KÝ THÀNH VIÊN</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger small'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-2">
                <label class="form-label small fw-bold">Họ và tên</label>
                <input type="text" name="txtName" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Email</label>
                <input type="email" name="txtEmail" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Tên đăng nhập</label>
                <input type="text" name="txtUser" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Mật khẩu</label>
                <input type="password" name="txtPass" class="form-control rounded-pill px-3" required>
            </div>
            <button type="submit" name="btnRegister" class="btn btn-success w-100 rounded-pill fw-bold">XÁC NHẬN ĐĂNG KÝ</button>
            <p class="text-center mt-3 small">Đã có tài khoản? <a href="login_user.php">Đăng nhập</a></p>
        </form>
    </div>
</body>
</html>