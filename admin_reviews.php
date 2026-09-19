<?php 
session_start();
include 'db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); 
    exit();
}

// --- 1. TẠO MÃ BẢO MẬT CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- 2. XỬ LÝ POST: DUYỆT, ẨN HOẶC XÓA BÌNH LUẬN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật!'); window.location.href='admin_reviews.php';</script>");
    }

    if (isset($_POST['action']) && isset($_POST['review_id'])) {
        $id = intval($_POST['review_id']);

        if ($_POST['action'] === 'approve') {
            // Cho phép hiển thị lên web
            $sql_update = "UPDATE reviews SET Status = 1 WHERE ReviewID = ?";
            $stmt = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        } 
        elseif ($_POST['action'] === 'hide') {
            // Ẩn khỏi web (Chờ duyệt)
            $sql_update = "UPDATE reviews SET Status = 0 WHERE ReviewID = ?";
            $stmt = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }
        elseif ($_POST['action'] === 'delete') {
            // Xóa vĩnh viễn
            $sql_delete = "DELETE FROM reviews WHERE ReviewID = ?";
            $stmt = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }
        
        header("Location: admin_reviews.php");
        exit();
    }
}

// --- 3. LẤY DANH SÁCH BÌNH LUẬN (Gắn với tên khách và tên Tour) ---
$sql_reviews = "SELECT r.*, u.FullName, t.TourName 
                FROM reviews r 
                JOIN users u ON r.UserID = u.UserID 
                JOIN tours t ON r.TourID = t.TourID 
                ORDER BY r.ReviewDate DESC";
$result = mysqli_query($conn, $sql_reviews);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bình luận - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .star-rating { color: #ffc107; font-size: 0.9rem; }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-star-half text-warning me-2"></i> Kiểm Duyệt Bình Luận</h2>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <div class="table-container">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="15%">Khách hàng</th>
                    <th width="20%">Tour đánh giá</th>
                    <th width="12%">Đánh giá</th>
                    <th width="30%">Nội dung bình luận</th>
                    <th width="10%">Trạng thái</th>
                    <th width="13%" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $is_approved = $row['Status'] == 1;
                        ?>
                        <tr class="<?php echo $is_approved ? '' : 'table-warning bg-opacity-25'; ?>">
                            <td>
                                <strong><?php echo htmlspecialchars($row['FullName']); ?></strong><br>
                                <small class="text-muted"><?php echo date('H:i d/m/Y', strtotime($row['ReviewDate'])); ?></small>
                            </td>
                            <td>
                                <span class="fw-bold text-primary" style="font-size: 0.9rem;"><?php echo htmlspecialchars($row['TourName']); ?></span>
                            </td>
                            <td>
                                <div class="star-rating">
                                    <?php 
                                    $rating = intval($row['Rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="bi bi-star-fill"></i>';
                                        } else {
                                            echo '<i class="bi bi-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($row['Comment']); ?>
                                </div>
                            </td>
                            <td>
                                <?php if($is_approved): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Đã duyệt</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="bi bi-eye-slash"></i> Đang ẩn</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="admin_reviews.php" class="d-inline mb-0">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="review_id" value="<?php echo $row['ReviewID']; ?>">
                                    
                                    <?php if($is_approved): ?>
                                        <!-- Nếu đang hiện thì có nút Ẩn -->
                                        <button type="submit" name="action" value="hide" class="btn btn-sm btn-outline-warning" title="Ẩn bình luận này khỏi web">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Nếu đang ẩn thì có nút Duyệt -->
                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Duyệt hiển thị lên web">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Nút Xóa -->
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');" title="Xóa vĩnh viễn">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-5 text-muted fst-italic'>Chưa có bình luận nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>