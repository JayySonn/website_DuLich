<?php
session_start();
include 'db.php';

if (isset($_POST['btnLogin'])) {
    // Dùng trim() để xóa khoảng trắng thừa nếu có vô tình nhập sai
    $user = trim($_POST['txtUser']);
    $pass = trim($_POST['txtPass']);

    // NÂNG CẤP BẢO MẬT: Dùng Prepared Statements chống SQL Injection tuyệt đối
    $sql = "SELECT * FROM Admins WHERE Username = ? AND Password = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    // Gắn tham số (biến $user và $pass) vào câu lệnh một cách an toàn
    mysqli_stmt_bind_param($stmt, "ss", $user, $pass);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $row['AdminID'];
        $_SESSION['admin_name'] = $row['FullName'];
        
        // CHUYỂN HƯỚNG TỚI TRANG DASHBOARD
        header("Location: admin_dashboard.php"); 
        exit(); // Bắt buộc phải có exit() sau header để dừng code chạy ngầm
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; }
        .login-card { width: 100%; max-width: 400px; padding: 30px; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="card login-card bg-white text-center">
            <h3 class="fw-bold mb-4 text-primary">ADMIN LOGIN</h3>
            <?php if(isset($error)) echo "<div class='alert alert-danger small'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label small fw-bold">Tên đăng nhập</label>
                    <input type="text" name="txtUser" class="form-control rounded-pill px-3" required>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label small fw-bold">Mật khẩu</label>
                    <input type="password" name="txtPass" class="form-control rounded-pill px-3" required>
                </div>
                <button type="submit" name="btnLogin" class="btn btn-primary w-100 rounded-pill fw-bold py-2">ĐĂNG NHẬP</button>
                <a href="index.php" class="d-block mt-3 text-muted small text-decoration-none">← Quay về trang chủ</a>
            </form>
        </div>
    </div>
</body>
</html>