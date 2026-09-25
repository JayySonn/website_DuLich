<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

// LÍNH GÁC MỚI: Đẩy về đúng cửa login_admin.php
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('CẢNH BÁO: Bạn chưa đăng nhập trang Quản trị!'); window.location.href='login_admin.php';</script>";
    exit();
}

// Lấy TourID từ URL
if (!isset($_GET['tour_id'])) {
    header("Location: admin_list_tours.php");
    exit();
}
$tour_id = intval($_GET['tour_id']);

// Lấy thông tin Tour để hiển thị tiêu đề
$sql_tour = "SELECT TourName FROM Tours WHERE TourID = ?";
$stmt_t = mysqli_prepare($conn, $sql_tour);
mysqli_stmt_bind_param($stmt_t, "i", $tour_id);
mysqli_stmt_execute($stmt_t);
$tour = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_t));

if (!$tour) {
    die("Không tìm thấy tour!");
}

// Tạo CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Xử lý thêm ảnh mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_image'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật!'); window.location.href='admin_tour_images.php?tour_id=$tour_id';</script>");
    }

    $image_url = trim($_POST['image_url']);
    if (!empty($image_url)) {
        $sql_add = "INSERT INTO tour_images (TourID, ImageURL) VALUES (?, ?)";
        $stmt_add = mysqli_prepare($conn, $sql_add);
        mysqli_stmt_bind_param($stmt_add, "is", $tour_id, $image_url);
        mysqli_stmt_execute($stmt_add);
        header("Location: admin_tour_images.php?tour_id=$tour_id");
        exit();
    }
}

// Xử lý xóa ảnh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image_id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Lỗi bảo mật!");
    }
    $img_id = intval($_POST['delete_image_id']);
    $sql_del = "DELETE FROM tour_images WHERE ImageID = ? AND TourID = ?";
    $stmt_del = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt_del, "ii", $img_id, $tour_id);
    mysqli_stmt_execute($stmt_del);
    header("Location: admin_tour_images.php?tour_id=$tour_id");
    exit();
}

// Lấy danh sách ảnh phụ của tour này
$sql_images = "SELECT * FROM tour_images WHERE TourID = ?";
$stmt_imgs = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_imgs, "i", $tour_id);
mysqli_stmt_execute($stmt_imgs);
$list_images = mysqli_stmt_get_result($stmt_imgs);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Thư viện Ảnh - <?php echo htmlspecialchars($tour['TourName']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .img-card { position: relative; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); background: white; }
        /* Thêm hiệu ứng cho ảnh để Admin biết có thể click */
        .img-card img { width: 100%; height: 160px; object-fit: cover; cursor: pointer; transition: 0.3s; }
        .img-card img:hover { opacity: 0.8; }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="bi bi-images text-primary me-2"></i> Thư Viện Ảnh Tour</h2>
            <p class="text-muted mb-0">Đang quản lý ảnh cho: <strong class="text-danger"><?php echo htmlspecialchars($tour['TourName']); ?></strong></p>
        </div>
        <a href="admin_list_tours.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại danh sách Tour</a>
    </div>

    <!-- Form thêm ảnh -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <h5 class="fw-bold mb-3">Thêm ảnh phụ mới</h5>
        <form method="POST" action="admin_tour_images.php?tour_id=<?php echo $tour_id; ?>" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="col-md-10">
                <input type="url" name="image_url" class="form-control py-2" placeholder="Dán đường dẫn URL hình ảnh vào đây (https://...)" required>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" name="add_image" class="btn btn-primary fw-bold">Thêm Ảnh</button>
            </div>
        </form>
    </div>

    <!-- Danh sách ảnh hiện tại -->
    <div class="row g-4">
        <?php if(mysqli_num_rows($list_images) > 0): ?>
            <?php while($img = mysqli_fetch_assoc($list_images)): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="img-card p-2">
                        <!-- Gọi hàm showImageModal khi click -->
                        <img src="<?php echo htmlspecialchars($img['ImageURL']); ?>" alt="Tour Image" onerror="this.src='https://placehold.co/300x160?text=Lỗi+Ảnh';" onclick="showImageModal(this.src)" title="Nhấn để xem rõ hơn">
                        <div class="p-2 d-flex justify-content-between align-items-center">
                            <small class="text-muted text-truncate" style="max-width: 140px;"><?php echo htmlspecialchars($img['ImageURL']); ?></small>
                            <form method="POST" action="admin_tour_images.php?tour_id=<?php echo $tour_id; ?>" class="mb-0">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="delete_image_id" value="<?php echo $img['ImageID']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa ảnh này khỏi thư viện?');" title="Xóa ảnh">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted fst-italic bg-white rounded-4 shadow-sm">
                Tour này chưa có ảnh phụ nào trong thư viện. Hãy thêm ảnh ở khung phía trên!
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL XEM ẢNH CHUYÊN NGHIỆP -->
<div class="modal fade" id="imageLightbox" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white bg-dark p-2" data-bs-dismiss="modal" aria-label="Close" style="border-radius: 50%;"></button>
            </div>
            <div class="modal-body text-center p-0 mt-2">
                <!-- Ảnh sẽ tự động co giãn không vượt quá 85% chiều cao màn hình -->
                <img id="lightboxImg" src="" class="img-fluid shadow-lg rounded" style="max-height: 85vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Hàm nhận link ảnh và mở cửa sổ nổi
    function showImageModal(src) {
        document.getElementById('lightboxImg').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageLightbox'));
        myModal.show();
    }
</script>
</body>
</html>