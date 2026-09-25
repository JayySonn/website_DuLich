<?php 
session_start();
include 'db.php'; 

if(isset($_GET['id'])) {
    // VÁ LỖ HỔNG: Bắt buộc dùng intval() để ép kiểu ID thành số nguyên
    $id = intval($_GET['id']);
    $sql = "SELECT Tours.*, Categories.CategoryName FROM Tours 
            LEFT JOIN Categories ON Tours.CategoryID = Categories.CategoryID WHERE TourID = $id";
    $result = mysqli_query($conn, $sql);
    $tour = mysqli_fetch_assoc($result);
    if(!$tour) die("Không tìm thấy tour!");
} else { 
    header("Location: index.php"); 
    exit(); 
}

$is_fixed_date = false;
$fixed_date_value = "";
$schedule = trim($tour['DepartureSchedule']); 
$is_expired = false; // Biến cờ hiệu kiểm tra hết hạn

if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $schedule)) {
    $date_parts = explode('/', $schedule);
    $fixed_date_value = $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
    $is_fixed_date = true;

    // KIỂM TRA NGÀY HẾT HẠN
    $today = date('Y-m-d');
    if (strtotime($fixed_date_value) < strtotime($today)) {
        $is_expired = true; // Đánh dấu là đã hết hạn
    }
}

$max_allowed = isset($tour['MaxPeople']) ? $tour['MaxPeople'] : 10;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết: <?php echo htmlspecialchars($tour['TourName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Lexend', sans-serif; }
        .tour-img { width: 100%; height: 500px; object-fit: cover; border-radius: 20px; }
        .price-tag { color: #ff4757; font-size: 35px; font-weight: 800; }
        .sticky-form { position: sticky; top: 100px; }
        .review-card { border-left: 5px solid #0d6efd; background: #fff; margin-bottom: 15px; border-radius: 15px !important; }
        .btn-quantity { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px !important; }
        input[readonly] { background-color: #f1f3f5 !important; cursor: not-allowed; }
        #qr-section { display: none; border: 2px dashed #0d6efd; background: #f0f7ff; }
        .gallery-img { height: 100px; object-fit: cover; cursor: pointer; transition: 0.3s; border-radius: 10px; }
        .gallery-img:hover { opacity: 0.8; transform: scale(1.02); }
    </style>
</head>
<body>

    <div class="container mt-5 mb-5">
        <a href="index.php" class="btn btn-white shadow-sm mb-4 rounded-pill px-4 bg-white">
            <i class="bi bi-arrow-left"></i> Quay lại trang chủ
        </a>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="bg-white p-4 shadow-sm rounded-4 mb-4 border-0">
                    <!-- ẢNH CHÍNH CỦA TOUR -->
                    <img src="<?php echo htmlspecialchars($tour['ImageURL']); ?>" class="tour-img mb-3" alt="Tour Image" onclick="showImageModal(this.src)" style="cursor: pointer;" title="Nhấn để phóng to">
                    
                    <!-- HIỂN THỊ ALBUM ẢNH PHỤ TỪ BẢNG tour_images -->
                    <?php
                    $sql_extra_imgs = "SELECT * FROM tour_images WHERE TourID = $id";
                    $res_extra_imgs = mysqli_query($conn, $sql_extra_imgs);
                    if (mysqli_num_rows($res_extra_imgs) > 0) {
                        echo '<div class="row g-2 mb-4">';
                        while ($ex_img = mysqli_fetch_assoc($res_extra_imgs)) {
                            // SỬ DỤNG HÀM showImageModal ĐỂ BẬT CỬA SỔ NỔI XEM ẢNH
                            echo '<div class="col-3 col-md-2">
                                    <img src="' . htmlspecialchars($ex_img['ImageURL']) . '" class="w-100 gallery-img shadow-sm" onclick="showImageModal(this.src)" title="Nhấn để phóng to">
                                  </div>';
                        }
                        echo '</div>';
                    }
                    ?>

                    <h1 class="fw-bold mb-3 mt-2"><?php echo htmlspecialchars($tour['TourName']); ?></h1>
                    
                    <div class="d-flex flex-wrap gap-4 mb-4 text-muted border-bottom pb-3">
                        <span><i class="bi bi-clock text-primary me-1"></i> <?php echo htmlspecialchars($tour['Duration']); ?></span>
                        <span><i class="bi bi-calendar-check text-primary me-1"></i> 
                            Lịch: <?php echo (!empty($tour['DepartureSchedule'])) ? htmlspecialchars($tour['DepartureSchedule']) : "Liên hệ"; ?>
                        </span>
                        <span>
                            <i class="bi bi-geo-alt-fill text-primary"></i> 
                            <strong>Khởi hành:</strong> <?php echo htmlspecialchars($tour['DepartureLocation']); ?>
                        </span>
                    </div>
                    
                    <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-journal-text me-2"></i>Lịch trình trải nghiệm</h4>
                    <div class="lh-lg text-secondary mb-5" style="text-align: justify;">
                        <?php echo nl2br(htmlspecialchars($tour['Description'])); ?>
                    </div>
                </div>

                <div class="bg-white p-4 shadow-sm rounded-4 border-0">
                    <h4 class="fw-bold mb-4 text-primary"><i class="bi bi-chat-left-heart me-2 text-danger"></i>Đánh giá từ khách hàng</h4>
                    
                    <!-- Hiển thị thông báo nếu vừa gửi đánh giá xong -->
                    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'review_sent'): ?>
                        <div class="alert alert-success rounded-pill fw-bold small text-center mb-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Cảm ơn bạn! Đánh giá đã được gửi và đang chờ Admin duyệt.
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Form gửi bình luận chuyển sang xử lý ở file process_review.php -->
                        <form action="process_review.php" method="POST" class="mb-5 p-4 border-0 rounded-4 bg-light shadow-sm">
                            <input type="hidden" name="tour_id" value="<?php echo $id; ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Xếp hạng của bạn:</label>
                                <select name="rating" class="form-select border-0 shadow-sm rounded-3 w-50">
                                    <option value="5">⭐⭐⭐⭐⭐ Tuyệt vời</option>
                                    <option value="4">⭐⭐⭐⭐ Rất tốt</option>
                                    <option value="3">⭐⭐⭐ Bình thường</option>
                                    <option value="2">⭐⭐ Kém</option>
                                    <option value="1">⭐ Rất tệ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <textarea name="comment" class="form-control border-0 shadow-sm rounded-4" rows="3" placeholder="Chia sẻ trải nghiệm của bạn..." required></textarea>
                            </div>
                            <button type="submit" name="btnReview" class="btn btn-primary px-5 rounded-pill fw-bold">Gửi đánh giá</button>
                            <small class="d-block mt-2 text-muted fst-italic">Bình luận của bạn sẽ được hiển thị sau khi Admin duyệt.</small>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning rounded-pill text-center small mb-4">
                            Vui lòng <a href="login_user.php" class="fw-bold">đăng nhập</a> để đánh giá tour này.
                        </div>
                    <?php endif; ?>

                    <div class="review-list">
                        <?php
                        // Chỉ lấy các bình luận có Status = 1 (Đã duyệt)
                        $sql_review = "SELECT Reviews.*, Users.FullName FROM Reviews 
                                       JOIN Users ON Reviews.UserID = Users.UserID 
                                       WHERE TourID = $id AND Status = 1 ORDER BY ReviewDate DESC";
                        $res_review = mysqli_query($conn, $sql_review);
                        
                        if(mysqli_num_rows($res_review) > 0) {
                            while($rev = mysqli_fetch_assoc($res_review)) {
                                ?>
                                <div class='card review-card border-0 shadow-sm p-4 mb-3'>
                                    <div class='d-flex justify-content-between align-items-center mb-2'>
                                        <strong><?php echo htmlspecialchars($rev['FullName']); ?></strong>
                                        <span class='text-warning small'><?php echo str_repeat("⭐", $rev['Rating']); ?></span>
                                    </div>
                                    <p class='mb-1 text-secondary small'><?php echo htmlspecialchars($rev['Comment']); ?></p>
                                    <div class='text-end small text-muted'><?php echo date('d/m/Y', strtotime($rev['ReviewDate'])); ?></div>
                                </div>
                                <?php
                            }
                        } else { 
                            echo "<p class='text-center py-4 text-muted'>Chưa có đánh giá nào cho tour này.</p>"; 
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-form">
                    <div class="card shadow border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-danger text-white text-center py-3">
                            <h5 class="fw-bold mb-0 text-uppercase">Đặt Tour Ngay</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <p class="price-tag mb-0" id="display-total"><?php echo number_format($tour['Price']); ?>đ</p>
                                <span class="badge bg-light text-dark border small rounded-pill">Giá cho <span id="people-count">1</span> khách</span>
                            </div>
                            
                            <form action="process_booking.php" method="POST">
                                <input type="hidden" name="tour_id" value="<?php echo $tour['TourID']; ?>">
                                <input type="hidden" id="base-price" value="<?php echo $tour['Price']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Họ tên người đặt:</label>
                                    <input type="text" name="customer_name" class="form-control rounded-3 border-0 bg-light py-2" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Số điện thoại :</label>
                                    <input type="tel" name="customer_phone" class="form-control rounded-3 border-0 bg-light py-2" 
                                           placeholder="VD: 0912345678" 
                                           pattern="^(03|05|07|08|09)[0-9]{8}$" 
                                           title="Vui lòng nhập đúng 10 chữ số thuộc các nhà mạng Việt Nam (bắt đầu bằng 03, 05, 07, 08, 09)" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted">Ngày khởi hành:</label>
                                    <?php if($is_fixed_date): ?>
                                        <input type="date" class="form-control rounded-3 border-0 py-2" value="<?php echo $fixed_date_value; ?>" readonly>
                                        <input type="hidden" name="departure_date" value="<?php echo $fixed_date_value; ?>">
                                        
                                        <?php if($is_expired): ?>
                                            <small class="text-danger fw-bold d-block mt-1" style="font-size: 0.8rem;"><i class="bi bi-exclamation-triangle-fill"></i> Tour này đã qua ngày khởi hành!</small>
                                        <?php else: ?>
                                            <small class="text-primary fw-bold" style="font-size: 0.7rem;">* Tour khởi hành cố định ngày <?php echo $schedule; ?></small>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <input type="date" name="departure_date" class="form-control rounded-3 border-0 bg-light py-2" required min="<?php echo date('Y-m-d'); ?>">
                                        <small class="text-muted fst-italic" style="font-size: 0.7rem;">* Lịch dự kiến: <?php echo $schedule; ?></small>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted d-block text-center">Số người (Tối đa <?php echo $max_allowed; ?>):</label>
                                    <div class="input-group justify-content-center bg-light p-2 rounded-3">
                                        <button class="btn btn-outline-secondary btn-quantity border-0 bg-white shadow-sm" type="button" onclick="updateQty(-1)">-</button>
                                        <input type="number" id="qty" name="quantity" class="form-control text-center fw-bold border-0 bg-transparent" value="1" min="1" max="<?php echo $max_allowed; ?>" readonly>
                                        <button class="btn btn-outline-secondary btn-quantity border-0 bg-white shadow-sm" type="button" onclick="updateQty(1)">+</button>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted">Phương thức thanh toán:</label>
                                    <select name="payment_method" id="payment_method" class="form-select rounded-3 border-0 bg-light" onchange="toggleQR()">
                                        <option value="Tiền mặt">Thanh toán tại văn phòng</option>
                                        <option value="Chuyển khoản">Chuyển khoản (Quét QR)</option>
                                    </select>
                                </div>

                                <div id="qr-section" class="p-3 rounded-4 mb-4 text-center">
                                    <p class="small fw-bold text-primary mb-2">QUÉT MÃ ĐỂ THANH TOÁN ẢO</p>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Thanh+Toan+Tour+<?php echo $id; ?>" class="img-fluid mb-2 rounded-3 shadow-sm">
                                    <div class="small text-dark">STK: <strong>123456789</strong> - MB Bank<br>Tên: <strong>NGUYEN NGOC SON</strong></div>
                                </div>

                                <div class="d-grid mt-3">
                                    <?php if($is_expired): ?>
                                        <!-- NÚT KHÓA NẾU TOUR ĐÃ HẾT HẠN -->
                                        <button type="button" class="btn btn-secondary btn-lg fw-bold rounded-pill py-3" disabled>
                                            <i class="bi bi-calendar-x me-2"></i>ĐÃ HẾT HẠN KHỞI HÀNH
                                        </button>
                                    <?php elseif(isset($_SESSION['user_id'])): ?>
                                        <button type="submit" name="btnBooking" class="btn btn-danger btn-lg fw-bold rounded-pill shadow-sm py-3">XÁC NHẬN ĐẶT TOUR</button>
                                    <?php else: ?>
                                        <a href="login_user.php" class="btn btn-outline-danger btn-lg fw-bold rounded-pill py-3">ĐĂNG NHẬP ĐỂ ĐẶT</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL XEM ẢNH CHUYÊN NGHIỆP -->
    <div class="modal fade" id="imageLightbox" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0 justify-content-end">
                    <button type="button" class="btn btn-dark border border-2 border-white rounded-circle shadow d-flex align-items-center justify-content-center p-0" data-bs-dismiss="modal" style="width: 25px; height: 25px; transition: 0.3s;">
                        <i class="bi bi-x-lg text-white fs-5"></i>
                    </button>
                </div>
                <div class="modal-body text-center p-0 mt-2">
                    <img id="lightboxImg" src="" class="img-fluid shadow-lg rounded" style="max-height: 85vh; object-fit: contain; background-color: rgba(0,0,0,0.7);">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateQty(val) {
            let qtyInput = document.getElementById('qty');
            let maxAllowed = parseInt(qtyInput.getAttribute('max')); 
            let currentQty = parseInt(qtyInput.value);
            let newQty = currentQty + val;

            if (newQty >= 1 && newQty <= maxAllowed) {
                qtyInput.value = newQty;
                document.getElementById('people-count').innerText = newQty;
                let basePrice = parseInt(document.getElementById('base-price').value);
                let total = basePrice * newQty;
                document.getElementById('display-total').innerText = total.toLocaleString('vi-VN') + 'đ';
            } else if (newQty > maxAllowed) { 
                alert("Rất tiếc! Tour này chỉ cho phép đặt tối đa " + maxAllowed + " người."); 
            }
        }
        function toggleQR() {
            let method = document.getElementById('payment_method').value;
            document.getElementById('qr-section').style.display = (method === 'Chuyển khoản') ? 'block' : 'none';
        }

        function showImageModal(src) {
            document.getElementById('lightboxImg').src = src;
            var myModal = new bootstrap.Modal(document.getElementById('imageLightbox'));
            myModal.show();
        }
    </script>
</body>
</html>