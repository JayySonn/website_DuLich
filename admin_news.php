<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// LÍNH GÁC MỚI: Đẩy về đúng cửa login_admin.php
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('CẢNH BÁO: Bạn chưa đăng nhập trang Quản trị!'); window.location.href='login_admin.php';</script>";
    exit(); 
}

include 'db.php';
include 'navbar_admin.php';

// --- 1. TẠO MÃ BẢO MẬT CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- 2. XỬ LÝ THÊM TIN TỨC MỚI (Dùng Prepared Statement) ---
if (isset($_POST['add_news'])) {
    // Kiểm tra Token bảo mật
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật!'); window.location.href='admin_news.php';</script>");
    }

    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $img = trim($_POST['image_url']);
    $url = trim($_POST['source_url']);
    $cat = trim($_POST['category']);
    $date = date('Y-m-d');

    $sql_add = "INSERT INTO News (Title, Description, ImageURL, SourceURL, Category, PublishDate) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_add = mysqli_prepare($conn, $sql_add);
    mysqli_stmt_bind_param($stmt_add, "ssssss", $title, $desc, $img, $url, $cat, $date);
    
    if (mysqli_stmt_execute($stmt_add)) {
        header("Location: admin_news.php?msg=success");
        exit();
    }
}

// --- 3. XỬ LÝ XÓA TIN TỨC (Dùng POST & Prepared Statement để bảo mật) ---
if (isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật!'); window.location.href='admin_news.php';</script>");
    }

    $id = intval($_POST['delete_id']);
    $sql_delete = "DELETE FROM News WHERE NewsID = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        header("Location: admin_news.php?msg=deleted");
        exit();
    }
}

// --- 4. LẤY DANH SÁCH TIN TỨC ---
$news_list = mysqli_query($conn, "SELECT * FROM News ORDER BY NewsID DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tin tức - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table img { width: 80px; height: 50px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Quản lý Tin tức & Cẩm nang</h2>
        </div>

        <!-- FORM THÊM TIN TỨC -->
        <div class="card mb-5">
            <div class="card-header bg-primary text-white fw-bold">Thêm tin tức / Link báo ngoài</div>
            <div class="card-body p-4">
                <form method="POST" action="admin_news.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tiêu đề bài viết</label>
                            <input type="text" name="title" class="form-control" placeholder="Ví dụ: Top 5 đặc sản Tiền Giang..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Chuyên mục</label>
                            <select name="category" class="form-select">
                                <option value="Cẩm nang">Cẩm nang</option>
                                <option value="Kinh nghiệm">Kinh nghiệm</option>
                                <option value="Lễ hội">Lễ hội</option>
                                <option value="Ẩm thực">Ẩm thực</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Link ảnh (URL)</label>
                            <input type="text" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Dán Link báo gốc (Nguồn)</label>
                            <input type="url" name="source_url" class="form-control text-primary" placeholder="Dán link từ VnExpress, Tuổi Trẻ... vào đây" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Mô tả ngắn gọn</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12 text-end mt-3">
                            <button type="submit" name="add_news" class="btn btn-primary px-5 rounded-pill shadow-sm">Đăng tin ngay</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH TIN TỨC -->
        <div class="card">
            <div class="table-responsive p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ảnh</th>
                            <th style="width: 40%;">Tiêu đề</th>
                            <th>Chuyên mục</th>
                            <th>Ngày đăng</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($news_list)) { ?>
                        <tr>
                            <td>
                                <!-- Kiểm tra nếu ảnh lỗi hoặc trống thì hiện ảnh mặc định -->
                                <img src="<?php echo !empty($row['ImageURL']) ? htmlspecialchars($row['ImageURL']) : 'https://placehold.co/80x50?text=No+Image'; ?>" alt="img" onerror="this.src='https://placehold.co/80x50?text=Error';">
                            </td>
                            <td>
                                <div class="fw-bold small"><?php echo htmlspecialchars($row['Title']); ?></div>
                                <a href="<?php echo htmlspecialchars($row['SourceURL']); ?>" target="_blank" class="text-muted x-small text-decoration-none">Xem nguồn <i class="bi bi-box-arrow-up-right"></i></a>
                            </td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['Category']); ?></span></td>
                            <td class="small"><?php echo date('d/m/Y', strtotime($row['PublishDate'])); ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="admin_edit_news.php?id=<?php echo $row['NewsID']; ?>" 
                                       class="btn btn-sm btn-outline-primary shadow-sm" 
                                       style="border-radius: 8px; padding: 5px 10px;"
                                       title="Chỉnh sửa bài viết">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- NÚT XÓA BẢO MẬT BẰNG FORM POST -->
                                    <form method="POST" action="admin_news.php" class="d-inline mb-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['NewsID']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" 
                                                style="border-radius: 8px; padding: 5px 10px;" 
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa tin này vĩnh viễn không?');"
                                                title="Xóa bài viết">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if (mysqli_num_rows($news_list) == 0) echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Chưa có tin tức nào.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Script cần thiết cho thanh Menu -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>