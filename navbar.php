<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow" style="background-color: #082b5f !important;">
    <div class="container">
         <a class="navbar-brand fw-bold" href="index.php"> <img src="https://cdn-icons-png.flaticon.com/128/5968/5968879.png" width="60" height="60" alt="">MIỀNTÂY TRAVEL</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <div class="navbar-nav ms-auto align-items-center">
                <a class="nav-link px-3 text-white" href="index.php">Trang Chủ</a>
                
                <div class="nav-item">
                    <a class="nav-link px-3 text-white" href="about.php">Giới Thiệu</a>
                </div>

                <div class="nav-item">
                    <a class="nav-link px-3 text-white" href="news.php">Tin Tức</a>
                </div>

                <div class="nav-item">
                    <a class="nav-link px-3 text-white" href="contact.php">Liên Hệ</a>
                </div>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <a class="nav-link px-3 text-white" href="my_bookings.php">
                        <i class="bi bi-bag-check me-1"></i>Đơn hàng
                    </a>
                    <span class="nav-user-name ms-2 text-white">
                        <i class="bi bi-person-circle me-1"></i><?php echo $_SESSION['user_name'] ?? ''; ?>
                    </span>
                    <a class="nav-link text-warning ms-2 fw-bold" href="logout.php">Thoát</a>
                <?php else: ?>
                    <a class="nav-link px-3 text-white" href="login_user.php">Đăng nhập</a>
                    <a class="nav-link px-3 text-white border border-white rounded-pill ms-2" href="register.php">Đăng ký</a>
                <?php endif; ?>
                
                
            </div>
        </div>
    </div>
</nav>

<style>
    
    .navbar-nav .nav-link {
        transition: 0.3s;
        font-weight: 500;
    }
    .navbar-nav .nav-link:hover {
        color: #ffffff !important;
        opacity: 0.8;
        transform: translateY(-2px);
    }
    
    .navbar-nav .nav-link.active {
        font-weight: 700 !important;
        opacity: 1;
    }
</style>