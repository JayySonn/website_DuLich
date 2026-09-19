<?php 
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); 
    exit();
}
include 'db.php';

// 1. LẤY DỮ LIỆU CŨ CỦA BÀI VIẾT
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM News WHERE NewsID = $id");
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        header("Location: admin_news.php");
        exit();
    }
} else {
    header("Location: admin_news.php");
    exit();
}

// 2. XỬ LÝ CẬP NHẬT KHI BẤM NÚT LƯU
if (isset($_POST['btnUpdate'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $source_url = mysqli_real_escape_string($conn, $_POST['source_url']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql_update = "UPDATE News SET 
                    Title = '$title', 
                    Category = '$category', 
                    ImageURL = '$image_url', 
                    SourceURL = '$source_url', 
                    Description = '$description' 
                   WHERE NewsID = $id";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Cập nhật tin tức thành công!'); window.location='admin_news.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Tin Tức - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .edit-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-label { font-weight: 600; color: #444; }
    </style>
</head>
<body>

    <?php include 'navbar_admin.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card edit-card p-4">
                    <h3 class="fw-bold text-center text-primary mb-4">
                        <i class="bi bi-pencil-square me-2"></i>CHỈNH SỬA TIN TỨC
                    </h3>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề bài viết:</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($row['Title']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chuyên mục:</label>
                                <select name="category" class="form-select">
                                    <option value="Cẩm nang" <?php if($row['Category'] == 'Cẩm nang') echo 'selected'; ?>>Cẩm nang</option>
                                    <option value="Kinh nghiệm" <?php if($row['Category'] == 'Kinh nghiệm') echo 'selected'; ?>>Kinh nghiệm</option>
                                    <option value="Lễ hội" <?php if($row['Category'] == 'Lễ hội') echo 'selected'; ?>>Lễ hội</option>
                                    <option value="Ẩm thực" <?php if($row['Category'] == 'Ẩm thực') echo 'selected'; ?>>Ẩm thực</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Link ảnh đại diện (URL):</label>
                                <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($row['ImageURL']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link nguồn bài viết (URL báo ngoài):</label>
                            <input type="text" name="source_url" class="form-control" value="<?php echo htmlspecialchars($row['SourceURL']); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Mô tả ngắn:</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($row['Description']); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="btnUpdate" class="btn btn-primary fw-bold py-2">
                                <i class="bi bi-save me-2"></i>LƯU THAY ĐỔI
                            </button>
                            <a href="admin_news.php" class="btn btn-light fw-bold py-2">HỦY BỎ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>