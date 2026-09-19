<?php 
session_start();
include 'db.php'; 

// Tạo mã CSRF Token bảo mật form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    // Kiểm tra tính hợp lệ của Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_msg = "Lỗi bảo mật! Vui lòng tải lại trang và thử lại.";
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $message = trim($_POST['message']);
        
        if (!empty($name) && !empty($email) && !empty($message)) {
            // Nâng cấp: Dùng Prepared Statement để lưu vào CSDL
            $sql = "INSERT INTO contacts (FullName, Email, Phone, Message, Status, CreatedAt) VALUES (?, ?, ?, ?, 0, NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Cảm ơn $name! Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ liên hệ lại sớm nhất.";
            } else {
                $error_msg = "Hệ thống đang bận. Vui lòng thử lại sau.";
            }
        } else {
            $error_msg = "Vui lòng nhập đầy đủ Tên, Email và Nội dung tin nhắn.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - MIỀNTÂY TRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Lexend', sans-serif; background-color: #f4f7f9; }
        
        .contact-header {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.2)), 
                        url('https://images.unsplash.com/photo-1596401057633-54a8fe8ef647?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 140px 0 180px 0;
            color: white;
            clip-path: ellipse(150% 100% at 50% 0%);
        }

        .contact-header h1 {
            text-shadow: 2px 4px 10px rgba(0, 0, 0, 0.3);
        }

        .contact-card {
            border: none;
            border-radius: 25px;
            background: white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-top: -100px;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .info-box { background: #0d6efd; color: white; padding: 40px; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #eee; background: #fafafa; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); }
        
        .map-wrapper { border-radius: 25px; overflow: hidden; height: 400px; margin-top: 50px; border: 1px solid #ddd; }
        .main-footer { background-color: #212529; color: white; padding: 60px 0 30px 0; margin-top: 80px; }
        .copyright-box { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; margin-top: 40px; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="contact-header text-center">
        <div class="container">
            <h1 class="fw-bold display-4">Kết Nối Với Chúng Tôi</h1>
            <p class="lead opacity-75">Bạn cần hỗ trợ? Đội ngũ MiềnTây Travel luôn sẵn sàng giúp đỡ bạn 24/7</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="contact-card">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="info-box h-100 d-flex flex-column justify-content-center">
                        <h3 class="fw-bold mb-4">Thông tin liên hệ</h3>
                        <div class="mb-4 d-flex align-items-center">
                            <i class="bi bi-geo-alt fs-3 me-3"></i>
                            <span>Xã Mỹ Thành, Cai Lậy, Tiền Giang</span>
                        </div>
                        <div class="mb-4 d-flex align-items-center">
                            <i class="bi bi-telephone fs-3 me-3"></i>
                            <span>0368 457 106</span>
                        </div>
                        <div class="mb-4 d-flex align-items-center">
                            <i class="bi bi-envelope fs-3 me-3"></i>
                            <span>lienhe@mientaytravel.com</span>
                        </div>
                        <div class="mt-4 d-flex gap-3">
                            <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-white fs-4"><i class="bi bi-chat-dots-fill"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 p-5">
                    <h3 class="fw-bold mb-4 text-dark">Gửi tin nhắn cho chúng tôi</h3>
                    
                    <!-- VÙNG HIỂN THỊ THÔNG BÁO LỖI / THÀNH CÔNG -->
                    <?php if(!empty($success_msg)): ?>
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($error_msg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Tên của bạn <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Nhập tên..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Địa chỉ Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" placeholder="Email..." required>
                            </div>
                            <!-- THÊM TRƯỜNG SỐ ĐIỆN THOẠI VÀO ĐÂY -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" placeholder="Để lại số điện thoại để chúng tôi tư vấn nhanh nhất...">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Nội dung tin nhắn <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Bạn cần hỗ trợ..." required></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="send_message" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow">
                                    <i class="bi bi-send-fill me-2"></i> GỬI TIN NHẮN NGAY
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="map-wrapper shadow-sm">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3923.456!2d106.1234!3d10.4567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDI3JzI0LjEiTiAxMDbCsDA3JzIzLjQiRQ!5e0!3m2!1svi!2svn!4v1711550000000!5m2!1svi!2svn" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 small text-white-50">© 2026 MiềnTây Travel - Đồng hành cùng bạn trên mọi nẻo đường.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>