<?php 
session_start(); 
include 'db.php'; 
include 'navbar.php';

// --- XỬ LÝ LỌC TÌM KIẾM ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn,$_GET['search']) : "";
$price_range = isset($_GET['price_range']) ?$_GET['price_range'] : "";
$custom_price = isset($_GET['custom_price']) && $_GET['custom_price'] !== '' ? intval($_GET['custom_price']) : 0;
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Tạo chuỗi điều kiện động
$conditions = "1=1"; 

if (!empty($search)) {$conditions .= " AND TourName LIKE '%$search%'";}
if ($category_id > 0) {$conditions .= " AND CategoryID = $category_id";}

if (!empty($price_range)) {
    if ($price_range == "0-1000000") $conditions .= " AND Price < 1000000";
    elseif ($price_range == "1000000-3000000") $conditions .= " AND Price BETWEEN 1000000 AND 3000000";
    elseif ($price_range == "3000000-up") $conditions .= " AND Price > 3000000";
} elseif ($custom_price > 0) {$conditions .= " AND Price <= $custom_price";}

// --- THUẬT TOÁN PHÂN TRANG ---
$limit = 6; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1)$page = 1;
$offset = ($page - 1) *$limit;

$sql_count = "SELECT COUNT(*) as total FROM Tours WHERE $conditions";
$res_count = mysqli_query($conn,$sql_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_records =$row_count['total'];

$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM Tours WHERE $conditions ORDER BY TourID DESC LIMIT $offset,$limit";
$result = mysqli_query($conn,$sql);

$sql_cats = "SELECT * FROM Categories";
$res_cats = mysqli_query($conn,$sql_cats);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIỀNTÂY Travel - Du Lịch</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* TẮT HIỆU ỨNG TRƯỢT GIẬT CỦA BOOTSTRAP */
        html { scroll-behavior: auto !important; }
        
        body { font-family: 'Lexend', sans-serif; background-color: #f4f7f6; color: #333; }
        .navbar { background-color: #082b5f !important; transition: 0.3s; }
        .hero { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=2070&auto=format&fit=crop'); background-size: cover; background-position: center; background-attachment: fixed; height: 500px; display: flex; align-items: center; justify-content: center; color: white; margin-bottom: 0; }
        .search-wrapper { position: relative; margin-top: -50px; z-index: 20; }
        .card-tour { border: none; border-radius: 20px; overflow: hidden; transition: 0.4s; background: #fff; display: flex; flex-direction: column; height: 100%; }
        .card-tour:hover { transform: translateY(-12px); box-shadow: 0 25px 50px rgba(0,0,0,0.1) !important; }
        .card-img-container { position: relative; height: 230px; overflow: hidden; }
        .card-img-top { height: 100%; width: 100%; object-fit: cover; transition: 0.8s; }
        .price-badge { position: absolute; top: 15px; right: 15px; background: #082b5f; color: white; padding: 5px 15px; border-radius: 50px; font-weight: bold; font-size: 0.9rem; }
        .card-body { display: flex; flex-direction: column; flex-grow: 1; padding: 1.5rem; }
        .card-title { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3rem; }
        .card-tour .d-grid { margin-top: auto; padding-top: 1rem; }
        
        .pagination .page-link { border-radius: 50%; margin: 0 5px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: #082b5f; border: 1px solid #dee2e6; font-weight: 600; transition: 0.3s; }
        .pagination .page-item.active .page-link { background-color: #0d6efd; color: white; border-color: #0d6efd; box-shadow: 0 5px 15px rgba(13,110,253,0.3); }
        .pagination .page-link:hover:not(.active) { background-color: #e9ecef; }
        .pagination .page-item.disabled .page-link { color: #adb5bd; background-color: transparent; border-color: #dee2e6; }
        .social-icon { width: 45px; height: 45px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center; border-radius: 12px; color: rgba(255, 255, 255, 0.6); font-size: 20px; transition: all 0.3s ease; text-decoration: none; }
        .social-icon:hover { background: #0d6efd; color: white; transform: translateY(-5px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4); border-color: #0d6efd; }
        #chat-circle { transition: all 0.3s ease; }
        #chat-circle:hover { transform: scale(1.1) rotate(5deg); box-shadow: 0 15px 30px rgba(241, 253, 13, 0.86) !important; }
        
        /* Khoảng cách bù cho thanh Menu */
        #tour-section { scroll-margin-top: 100px; }
    </style>
</head>
<body>

    <header class="hero">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-3">Hành Trình Mơ Ước</h1>
            <p class="lead opacity-90">Khám phá văn hóa và vẻ đẹp con người Việt Nam</p>
        </div>
    </header>

    <div class="container search-wrapper">
        <div class="mx-auto position-relative" style="background: white; padding: 12px; border-radius: 50px; box-shadow: 0 15px 40px rgba(0,0,0,0.15); max-width: 1050px;">
            <form action="index.php#tour-section" method="GET" class="row g-0 align-items-center m-0">
                <div class="col-md-5 px-2">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 ps-3"><i class="bi bi-search text-primary fs-5"></i></span>
                        <input type="text" name="search" class="form-control shadow-none border-0 bg-transparent py-2" style="font-size: 1.05rem;" placeholder="Bạn muốn đi đâu?" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <div class="col-md-2 border-start border-2 border-light px-2">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-grid-fill text-muted" style="font-size: 0.9rem;"></i></span>
                        <select name="category" class="form-select shadow-none border-0 bg-transparent text-muted px-1" style="cursor: pointer; font-size: 0.9rem;">
                            <option value="0">Danh mục</option>
                            <?php if($res_cats): while($c = mysqli_fetch_assoc($res_cats)): ?>
                                <option value="<?php echo $c['CategoryID']; ?>" <?php echo ($category_id ==$c['CategoryID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['CategoryName']); ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 border-start border-2 border-light px-2">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-currency-dollar text-primary" style="font-size: 0.9rem;"></i></span>
                        <input type="hidden" id="price_range_input" name="price_range" value="<?php echo htmlspecialchars($price_range); ?>">
                        <input type="number" id="custom_price_input" name="custom_price" class="form-control shadow-none border-0 bg-transparent px-1" placeholder="Nhập mức giá..." min="0" style="font-size: 0.9rem;" value="<?php echo $custom_price > 0 ?$custom_price : ''; ?>" oninput="clearPreset()">
                    </div>
                </div>

                <div class="col-md-2 px-2 text-end">
                    <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" style="background-color: #0d6efd; border: none; font-size: 1rem;" type="submit">Tìm kiếm ngay</button>
                </div>
            </form>
        </div>
        
        <div class="text-center mt-3">
            <span class="badge rounded-pill fw-normal shadow-sm me-2 <?php echo $price_range == '0-1000000' ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" style="cursor: pointer; padding: 8px 15px; border: 1px solid #ddd;" onclick="setPricePreset('0-1000000')">Dưới 1 triệu</span>
            <span class="badge rounded-pill fw-normal shadow-sm me-2 <?php echo $price_range == '1000000-3000000' ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" style="cursor: pointer; padding: 8px 15px; border: 1px solid #ddd;" onclick="setPricePreset('1000000-3000000')">1 triệu - 3 triệu</span>
            <span class="badge rounded-pill fw-normal shadow-sm me-2 <?php echo $price_range == '3000000-up' ? 'bg-primary text-white' : 'bg-white text-dark'; ?>" style="cursor: pointer; padding: 8px 15px; border: 1px solid #ddd;" onclick="setPricePreset('3000000-up')">Trên 3 triệu</span>
            <a href="index.php" class="text-decoration-none small text-danger fw-bold ms-2"><i class="bi bi-arrow-counterclockwise"></i> Xóa bộ lọc</a>
        </div>
    </div>

    <!-- KHU VỰC DANH SÁCH TOUR -->
    <div class="container mt-5 pt-4" id="tour-section">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="fw-bold m-0"><?php echo empty($search) && empty($price_range) && $custom_price == 0 &&$category_id == 0 ? '<img src="https://cdn-icons-png.flaticon.com/128/9434/9434847.png" width="60" height="60"> TOUR PHỔ BIẾN': '<i class="bi bi-search"></i> KẾT QUẢ TÌM KIẾM'; ?></h2>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {$t_id = $row['TourID'];$sql_count = "SELECT SUM(Quantity) as booked FROM Bookings WHERE TourID = $t_id AND Status != N'Đã hủy'";
                    $res_count = mysqli_query($conn, $sql_count);$row_count = mysqli_fetch_assoc($res_count);$max = isset($row['MaxPeople']) ?$row['MaxPeople'] : 10;
                    $booked = ($row_count['booked']) ?$row_count['booked'] : 0;
                    $remain = $max -$booked;
                    ?>
                    <div class="col mb-4">
                        <div class="card card-tour shadow-sm border-0">
                            <div class="card-img-container">
                                <img src="<?php echo $row['ImageURL']; ?>" class="card-img-top">
                                <div class="price-badge"><?php echo number_format($row['Price']); ?> đ</div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3"><?php echo $row['TourName']; ?></h5>
                                <div class="d-flex flex-column gap-2 text-muted mb-4 small">
                                    <span><i class="bi bi-clock me-1 text-primary"></i> <?php echo $row['Duration']; ?></span>
                                    
                                    <!-- ĐÃ ĐỔI NHÃN VÀ THÊM FALLBACK LÀ "Theo yêu cầu" -->
                                    <span><i class="bi bi-calendar-event me-1 text-primary"></i> Ngày đi: <?php echo (!empty($row['DepartureSchedule'])) ?$row['DepartureSchedule'] : "Theo yêu cầu"; ?></span>
                                    
                                    <!-- ĐÃ ĐỔI NHÃN THÀNH "Nơi xuất phát" -->
                                    <span><i class="bi bi-geo-alt me-1 text-primary"></i> Nơi xuất phát: <?php echo (!empty($row['DepartureLocation'])) ?$row['DepartureLocation'] : "Liên hệ Admin"; ?></span>
                                    
                                    <span><i class="bi bi-people me-1 text-primary"></i> Còn trống: <b class="text-danger"><?php echo $remain; ?></b> / <?php echo$max; ?> khách</span>
                                </div>
                                <div class="d-grid">
                                    <a href="detail.php?id=<?php echo $row['TourID']; ?>" class="btn btn-outline-primary rounded-pill fw-bold">Xem Chi Tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='col-12 text-center py-5'><img src='https://cdn-icons-png.flaticon.com/512/6134/6134065.png' width='100' class='mb-3 opacity-50'><p class='fs-5 text-muted'>Rất tiếc, không tìm thấy tour nào phù hợp.</p></div>";
            }
            ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php 
                    $qs_array =$_GET;
                    unset($qs_array['page']);$qs = http_build_query($qs_array);$prefix = (!empty($qs) ? "&" . $qs : "") . "#tour-section";
                    ?>

                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link shadow-none" href="?page=<?php echo $page - 1 .$prefix; ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page ==$i) ? 'active' : ''; ?>">
                            <a class="page-link shadow-none" href="?page=<?php echo $i .$prefix; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($page >=$total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link shadow-none" href="?page=<?php echo $page + 1 .$prefix; ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    </div>

    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container text-md-start text-center">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h4 class="fw-bold text-primary mb-3"><img src="https://cdn-icons-png.flaticon.com/128/5968/5968879.png" width="50" height="50" alt="Logo"> MIỀNTÂY TRAVEL</h4>
                <p class="text-white-50 small pe-md-4">Uy tín - Chất lượng - Trải nghiệm tuyệt vời. Chúng tôi không chỉ bán những chuyến đi, chúng tôi trao gửi sự chân thành và lòng hiếu khách đặc trưng của người dân miền Tây đến với mọi hành trình.</p>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3 text-uppercase small">Thông tin liên lạc</h5>
                <p class="text-white-50 small mb-2"><i class="bi bi-geo-alt-fill me-2 text-primary"></i> Xã Mỹ Thành, Cai Lậy, Tiền Giang</p>
                <p class="text-white-50 small mb-2"><i class="bi bi-telephone-fill me-2 text-primary"></i> 0123 456 789 (Hỗ trợ 24/7)</p>
                <p class="text-white-50 small mb-2"><i class="bi bi-envelope-fill me-2 text-primary"></i> lienhe@mientaytravel.com</p>
            </div>

            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3 text-uppercase small">Kết nối với chúng tôi</h5>
                <div class="d-flex justify-content-md-start justify-content-center gap-3">
                    <a href="https://www.facebook.com/son.325890?locale=vi_VN" class="social-icon facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com/" class="social-icon instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.tiktok.com/@tiktokvn" class="social-icon ChatBox"><i class="bi bi-chat-dots-fill"></i></a>
                </div>
            </div>
        </div>
        <hr class="my-4 border-secondary opacity-25">
        <div class="row"><div class="col-12 text-center"><p class="mb-0 text-white-50 shadow-sm" style="font-size: 18px;">© 2026 I Love ❤️ <span class="text-white fw-bold">Miền Tây Travel</span></p></div></div>
    </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setPricePreset(range) {
            document.getElementById('price_range_input').value = range;
            document.getElementById('custom_price_input').value = ''; 
            document.forms[0].action = "index.php#tour-section"; 
            document.forms[0].submit(); 
        }
        function clearPreset() { document.getElementById('price_range_input').value = ''; }
    </script>

   <div id="chat-circle" style="position: fixed; bottom: 20px; right: 20px; width: 70px; height: 70px; z-index: 1000; cursor: pointer;">
    <div class="bg-primary rounded-circle w-100 h-100 d-flex align-items-center justify-content-center overflow-hidden border border-3 border-white shadow-lg">
        <img src="https://cdn-icons-png.flaticon.com/128/5968/5968879.png" style="width: 80%; height: 80%; object-fit: contain;">
    </div>
    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-success border border-light rounded-circle" style="width: 15px; height: 15px;"></span>
    </div>

    <div id="chat-box" class="card shadow-lg border-0" style="position: fixed; bottom: 100px; right: 20px; width: 350px; height: 450px; display: none; z-index: 1000; border-radius: 20px; overflow: hidden;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <span class="fw-bold"><i class="bi bi-robot me-2"></i>AI Hỗ Trợ Miền Tây Travel</span>
            <i class="bi bi-x-lg" id="close-chat" style="cursor: pointer;"></i>
        </div>
        <div class="card-body" id="chat-content" style="overflow-y: auto; background: #f8f9fa;">
            <div class="bot-msg mb-3"><span class="p-2 rounded-3 bg-white shadow-sm d-inline-block small border">Chào Sơn! Mình là AI của MienTay Travel. Bạn cần mình tư vấn tour nào không?</span></div>
        </div>
        <div class="card-footer bg-white border-0 p-3">
            <div class="input-group">
                <input type="text" id="user-input" class="form-control border-0 bg-light rounded-pill" placeholder="Nhập câu hỏi...">
                <button class="btn btn-primary rounded-circle ms-2" onclick="sendMsg()"><i class="bi bi-send-fill"></i></button>
            </div>
        </div>
    </div>

    <script>
    function toggleChat() {
        var chatBox = document.getElementById('chat-box');
        if (chatBox.style.display === 'none' || chatBox.style.display === '') { chatBox.style.display = 'flex'; chatBox.style.flexDirection = 'column'; } 
        else { chatBox.style.display = 'none'; }
    }
    document.getElementById('chat-circle').addEventListener('click', toggleChat);
    document.getElementById('close-chat').addEventListener('click', toggleChat);

    async function sendMsg() {
        let input = document.getElementById('user-input'); let content = document.getElementById('chat-content'); let message = input.value.trim();
        if (message !== "") {
            content.innerHTML += `<div class="text-end mb-3"><span class="p-2 rounded-3 bg-primary text-white d-inline-block small shadow-sm">${message}</span></div>`;
            input.value = ""; content.scrollTop = content.scrollHeight;
            let loadingId = 'loading-' + Date.now();
            content.innerHTML += `<div class="bot-msg mb-3" id="${loadingId}"><span class="p-2 rounded-3 bg-white shadow-sm d-inline-block small border italic"> đang trả lời...</span></div>`;
            content.scrollTop = content.scrollHeight;

            try {
                let response = await fetch('chat_ai.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: message }) });
                let result = await response.json();
                document.getElementById(loadingId).remove();
                content.innerHTML += `<div class="bot-msg mb-3"><span class="p-2 rounded-3 bg-white shadow-sm d-inline-block small border">${result.reply}</span></div>`;
                content.scrollTop = content.scrollHeight;
            } catch (error) { document.getElementById(loadingId).innerHTML = "Lỗi kết nối rồi Sơn ơi!"; }
        }
    }
    document.getElementById('user-input').addEventListener('keypress', function (e) { if (e.key === 'Enter') { sendMsg(); } });
    </script>
</body>
</html>