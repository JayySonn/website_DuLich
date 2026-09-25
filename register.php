<?php
include 'db.php';

if (isset($_POST['btnRegister'])) {
    // 1. Dùng trim() để cắt bỏ khoảng trắng thừa (chống nhập toàn dấu cách)
    $user = mysqli_real_escape_string($conn, trim($_POST['txtUser']));
    $name = mysqli_real_escape_string($conn, trim($_POST['txtName']));
    $email = mysqli_real_escape_string($conn, trim($_POST['txtEmail']));
    $raw_pass = trim($_POST['txtPass']);

    // Kiểm tra dữ liệu rỗng sau khi đã cắt khoảng trắng
    if (empty($user) || empty($name) || empty($email) || empty($raw_pass)) {
        $error = "Vui lòng điền đầy đủ thông tin, không dùng toàn khoảng trắng!";
    } 
    // 2. Bắt buộc mật khẩu phải từ 6 ký tự trở lên
    elseif (strlen($raw_pass) < 6) {
        $error = "Mật khẩu quá yếu! Vui lòng nhập ít nhất 6 ký tự.";
    }
    // Kiểm tra định dạng Email chuẩn
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Vui lòng nhập đúng định dạng email (ví dụ: mientay@gmail.com)";
    } 
    else {
        // Mã hóa mật khẩu sau khi đã qua bài kiểm tra độ dài
        $pass = password_hash($raw_pass, PASSWORD_DEFAULT); 

        // 3. Gộp kiểm tra trùng Username VÀ trùng Email vào 1 câu lệnh
        $check = mysqli_query($conn, "SELECT * FROM Users WHERE Username='$user' OR Email='$email'");
        
        if(mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            if ($row['Username'] === $user) {
                $error = "Tên đăng nhập này đã có người sử dụng!";
            } else {
                $error = "Email này đã được đăng ký cho một tài khoản khác!";
            }
        } else {
            // Mọi thứ hoàn hảo -> Lưu vào Database
            $sql = "INSERT INTO Users (Username, Password, FullName, Email, Role) 
                    VALUES ('$user', '$pass', '$name', '$email', 'Khách hàng')";
            
            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Đăng ký thành công! Hãy đăng nhập.'); window.location='login_user.php';</script>";
            } else {
                $error = "Lỗi đăng ký: " . mysqli_error($conn);
            }
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
        
        <?php if(isset($error)) echo "<div class='alert alert-danger small fw-bold text-center'>$error</div>"; ?>
        
        <form method="POST">
            <div class="mb-2">
                <label class="form-label small fw-bold">Họ và tên</label>
                <input type="text" name="txtName" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Email</label>
                <input type="email" name="txtEmail" class="form-control rounded-pill px-3" placeholder="ví dụ: ten@gmail.com" required>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-bold">Tên đăng nhập</label>
                <input type="text" name="txtUser" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Mật khẩu</label>
                <input type="password" name="txtPass" class="form-control rounded-pill px-3" placeholder="Ít nhất 6 ký tự" required>
            </div>
            <button type="submit" name="btnRegister" class="btn btn-success w-100 rounded-pill fw-bold">XÁC NHẬN ĐĂNG KÝ</button>
            <p class="text-center mt-3 small">Đã có tài khoản? <a href="login_user.php" class="text-success fw-bold text-decoration-none">Đăng nhập</a></p>
        </form>
    </div>
</body>
</html>