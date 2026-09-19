<?php 
session_start();
include 'db.php'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới thiệu - MIÊNTÂY TRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Lexend', sans-serif; background-color: #f8f9fa; }
       .about-header { 
            /* 1. Thay link ảnh chất lượng cao hơn */
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                        url('https://images.unsplash.com/photo-1583417319070-4a69db38a482?q=80&w=2070&auto=format&fit=crop');
            
            /* 2. Giúp ảnh cố định khi cuộn (tạo hiệu ứng Parallax nhìn rất sang) */
            background-attachment: fixed;
            background-position: bottom center;
            background-size: cover;
            background-repeat: no-repeat;

            /* 3. Tăng padding để banner hoành tráng hơn */
            padding: 120px 0;
            color: white; 
            text-align: center;
            
            /* 4. Loại bỏ box-shadow cũ, dùng linear-gradient ở trên hiệu quả hơn */
            box-shadow: none; 
        }
        .feature-box {
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: 0.3s;
            height: 100%;
        }
        .feature-box:hover { transform: translateY(-10px); }
        .icon-circle {
            width: 70px;
            height: 70px;
            background: #e7f1ff;
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; // Nếu Sơn đã tách riêng navbar ra file khác ?>

    <header class="about-header shadow">
        <div class="container">
            <h1 class="display-4 fw-bold">Về MiềnTây Travel</h1>
            <p class="lead">Từ miền sông nước hữu tình, cùng bạn đi khắp Việt Nam</p>
        </div>
    </header>

    <div class="container my-5 py-4">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Câu chuyện của chúng tôi</h2>
                <p class="text-muted">Bắt đầu từ những chuyến xuồng ba lá len lỏi giữa rừng tràm xanh ngắt, <strong>MiềnTây Travel</strong> ra đời với tình yêu mãnh liệt dành cho vẻ đẹp mộc mạc của vùng đất Chín Rồng. Chúng tôi không chỉ bán những chuyến đi, chúng tôi trao gửi sự chân thành và lòng hiếu khách đặc trưng của người dân miền Tây đến với mọi hành trình.</p>
                <p class="text-muted">Với tâm thế "Người con miền Tây đi khắp dải đất hình chữ S", chúng tôi đã không ngừng vươn mình, mở rộng bản đồ du lịch từ những cánh đồng lúa bạt ngàn phía Nam đến những đỉnh núi hùng vĩ tại Hà Giang hay kỳ quan Vịnh Hạ Long. Dù bạn muốn lên rừng hay xuống biển, <strong>MiềnTây Travel</strong> luôn đồng hành cùng bạn với dịch vụ tận tâm và uy tín nhất.</p>
                <div class="d-flex gap-3 mt-4">
                    <div class="text-center">
                        <h3 class="fw-bold text-primary">10k+</h3>
                        <small class="text-muted">Khách hàng</small>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <h3 class="fw-bold text-primary">50+</h3>
                        <small class="text-muted">Tour đa dạng</small>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center">
                        <h3 class="fw-bold text-primary">100%</h3>
                        <small class="text-muted">Hài lòng</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0 text-center">
                <img src="https://nhandan.vn/special/30-nam-mot-chang-duong-di-san-Vinh-Ha-Long/assets/HLCklusX0n/things-to-do-in-ha-long-bay-banner-1-1920x1080.jpg" alt="Du lịch miền tây" class="img-fluid rounded-4 shadow">
            </div>
        </div>

        <hr class="my-5">

        <h2 class="text-center fw-bold mb-5">Tại sao nên chọn MiềnTây Travel?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon-circle"><i class="bi bi-shield-check"></i></div>
                    <h5 class="fw-bold">An toàn tuyệt đối</h5>
                    <p class="text-muted small">Chúng tôi cam kết an toàn cho du khách trên mọi nẻo đường với đội ngũ hướng dẫn viên giàu kinh nghiệm.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon-circle"><i class="bi bi-tag"></i></div>
                    <h5 class="fw-bold">Giá cả cạnh tranh</h5>
                    <p class="text-muted small">Nhiều lựa chọn tour từ bình dân đến cao cấp, đảm bảo chi phí hợp lý nhất cho mọi đối tượng.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="icon-circle"><i class="bi bi-heart"></i></div>
                    <h5 class="fw-bold">Hỗ trợ tận tâm</h5>
                    <p class="text-muted small">Đội ngũ CSKH luôn sẵn sàng lắng nghe và giải đáp mọi thắc mắc của bạn 24/7.</p>
                </div>
            </div>
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