<?php 
session_start();
include 'db.php'; 


$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$sql = "SELECT * FROM News WHERE Status = 1";
if (!empty($search)) {
    $sql .= " AND (Title LIKE '%$search%' OR Category LIKE '%$search%')";
}
$sql .= " ORDER BY PublishDate DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức & Cẩm nang - MIỀNTÂY TRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Lexend', sans-serif; background-color: #f8f9fa; }
        .news-card {
            border: none; border-radius: 15px; overflow: hidden;
            transition: 0.3s; background: white; height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: relative;
        }
        .news-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .news-img { height: 200px; object-fit: cover; width: 100%; }
        .category-badge {
            position: absolute; top: 15px; left: 15px;
            background: #0d6efd; color: white; padding: 5px 15px;
            border-radius: 50px; font-size: 12px; font-weight: 600; z-index: 10;
        }
        .news-title {
            font-size: 1.1rem; font-weight: 700; margin-bottom: 10px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; color: #333; text-decoration: none;
        }
        .news-title:hover { color: #0d6efd; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="row align-items-center mb-5">
            <div class="col-md-8">
                <h2 class="fw-bold"><i class="bi bi-newspaper text-primary me-2"></i>Tin Tức & Cẩm Nang Du Lịch</h2>
                <p class="text-muted">Khám phá vẻ đẹp và văn hóa miền Tây sông nước qua những bài viết mới nhất.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <form action="news.php" method="GET" class="input-group">
                    <input type="text" name="search" class="form-control border-0 shadow-sm" placeholder="Tìm bài viết..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <div class="row g-4">
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-md-4">
                    <div class="news-card">
                        <span class="category-badge"><?php echo $row['Category']; ?></span>
                        
                        <img src="<?php echo $row['ImageURL']; ?>" class="news-img" alt="news">
                        
                        <div class="card-body p-4">
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-calendar3 me-1"></i> <?php echo date('d/m/Y', strtotime($row['PublishDate'])); ?>
                            </small>
                            
                            <a href="<?php echo $row['SourceURL']; ?>" target="_blank" class="news-title">
                                <?php echo $row['Title']; ?>
                            </a>
                            
                            <p class="text-muted small mb-4">
                                <?php echo $row['Description']; ?>
                            </p>
                            
                            <a href="<?php echo $row['SourceURL']; ?>" target="_blank" class="text-primary fw-bold text-decoration-none small">
                                XEM THÊM <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center py-5'><p class='text-muted'>Chưa có bài viết nào phù hợp.</p></div>";
            }
            ?>
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