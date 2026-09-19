<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3"> 
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="admin_dashboard.php" style="letter-spacing: 1px; font-size: 1.3rem;"> 
            <i class="bi bi-shield-lock-fill me-2 text-info" style="font-size: 1.5rem;"></i> ADMIN DASHBOARD
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                
                <li class="nav-item">
                    <a class="nav-link px-3 text-white d-flex align-items-center" href="index.php">
                        <i class="bi bi-house-door me-2"></i> Xem Website
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link px-3 text-white d-flex align-items-center" href="admin_dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Thống kê 
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link px-3 text-white d-flex align-items-center" href="admin_bookings.php">
                        <i class="bi bi-cart-check me-2"></i> Đơn Hàng
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link px-3 text-white d-flex align-items-center" href="admin_list_tours.php">
                        <i class="bi bi-list-ul me-2"></i> Danh Sách Tour
                    </a>
                </li>

                <!-- MENU THẢ XUỐNG: QUẢN LÝ TƯƠNG TÁC -->
<li class="nav-item dropdown">
    <a class="nav-link px-3 text-white dropdown-toggle d-flex align-items-center" href="#" id="navbarInteract" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-chat-right-dots me-2"></i> Tương tác
    </a>
    <ul class="dropdown-menu dropdown-menu-dark shadow border-0 mt-2" aria-labelledby="navbarInteract">
        <li>
            <a class="dropdown-item py-2 d-flex align-items-center" href="admin_contacts.php">
                <i class="bi bi-inbox me-2"></i> Hộp thư Liên hệ
            </a>
        </li>
        <li>
            <a class="dropdown-item py-2 d-flex align-items-center" href="admin_news.php">
                <i class="bi bi-newspaper me-2"></i> Quản lý Tin tức
            </a>
        </li>
        <li><hr class="dropdown-divider border-secondary"></li>
        <li>
            <a class="dropdown-item py-2 d-flex align-items-center" href="admin_reviews.php">
                <i class="bi bi-star-half me-2"></i> Quản lý Bình luận
            </a>
        </li>
    </ul>
</li>
                
                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <a class="nav-link px-4 text-white border border-secondary rounded-pill d-flex align-items-center" href="admin_add_tour.php" style="background: rgba(255,255,255,0.05);">
                        <i class="bi bi-plus-circle me-2"></i> Thêm Tour
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</nav>

<style>
    html { overflow-y: scroll; } 
    
    .navbar-dark .navbar-nav .nav-link {
        color: #ffffff !important;
        font-size: 16px; 
        font-weight: 500;
        transition: 0.3s;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    
    .navbar-nav .nav-link i {
        font-size: 1.2rem; 
    }
    
    .navbar-dark .navbar-nav .nav-link:hover,
    .navbar-dark .navbar-nav .nav-link:focus {
        color: #0dcaf0 !important;
        transform: scale(1.05); 
    }

    .navbar-brand {
        text-shadow: 0 0 10px rgba(13, 202, 240, 0.3);
    }

    /* Hiệu ứng mượt mà khi hover vào mục con của dropdown */
    .dropdown-menu-dark .dropdown-item {
        transition: all 0.2s;
    }
    .dropdown-menu-dark .dropdown-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        padding-left: 20px; /* Hiệu ứng đẩy chữ nhẹ sang phải */
    }
</style>