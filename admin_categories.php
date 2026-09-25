<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// LÍNH GÁC MỚI: Kiểm tra thẻ 'admin_id' và đẩy về đúng cửa login_admin.php
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('CẢNH BÁO: Bạn chưa đăng nhập trang Quản trị!'); window.location.href='login_admin.php';</script>";
    exit(); 
}
include 'db.php';

// THÊM DANH MỤC
if(isset($_POST['btnAddCat'])) {
    // Cắt khoảng trắng dư thừa và chống SQL Injection
    $cat_name = mysqli_real_escape_string($conn, trim($_POST['cat_name']));
    
    if(!empty($cat_name)) {
        // Kiểm tra xem danh mục đã tồn tại chưa để tránh trùng lặp
        $check = mysqli_query($conn, "SELECT * FROM Categories WHERE CategoryName = '$cat_name'");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Danh mục này đã tồn tại!');</script>";
        } else {
            $sql = "INSERT INTO Categories (CategoryName) VALUES ('$cat_name')";
            if(mysqli_query($conn, $sql)) {
                echo "<script>alert('Đã thêm danh mục: $cat_name'); window.location.href='admin_categories.php';</script>";
            }
        }
    }
}

// XÓA DANH MỤC (Đã vá lỗ hổng SQL Injection)
if(isset($_GET['delete_id'])) {
    // BẮT BUỘC dùng intval() để ép biến $id từ URL thành số nguyên
    $id = intval($_GET['delete_id']);
    
    // Kiểm tra xem có tour nào đang dùng danh mục này không (Ràng buộc khóa ngoại)
    $check_tours = mysqli_query($conn, "SELECT * FROM Tours WHERE CategoryID = $id");
    if (mysqli_num_rows($check_tours) > 0) {
        echo "<script>alert('KHÔNG THỂ XÓA! Đang có Tour thuộc danh mục này. Hãy xóa Tour trước.'); window.location.href='admin_categories.php';</script>";
    } else {
        mysqli_query($conn, "DELETE FROM Categories WHERE CategoryID = $id");
        header("Location: admin_categories.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table thead { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-3">
                <a href="admin_add_tour.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại trang Thêm Tour</a>
            </div>

            <div class="card p-4 mb-4">
                <h3 class="fw-bold text-primary mb-4 text-center">QUẢN LÝ DANH MỤC DU LỊCH</h3>
                
                <form method="POST" class="row g-2">
                    <div class="col-md-9">
                        <input type="text" name="cat_name" class="form-control" placeholder="Nhập tên (VD: Du lịch Hành Hương...)" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" name="btnAddCat" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Thêm mới
                        </button>
                    </div>
                </form>
            </div>

            <div class="card p-4">
                <h5 class="fw-bold mb-3">Danh sách danh mục hiện có:</h5>
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="15%">ID</th>
                            <th>Tên danh mục</th>
                            <th width="20%" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = mysqli_query($conn, "SELECT * FROM Categories ORDER BY CategoryID DESC");
                        while($row = mysqli_fetch_assoc($res)) {
                            echo "<tr>
                                    <td>".$row['CategoryID']."</td>
                                    <td class='fw-semibold'>".$row['CategoryName']."</td>
                                    <td class='text-center'>
                                        <a href='admin_categories.php?delete_id=".$row['CategoryID']."' 
                                           onclick='return confirm(\"Xóa danh mục này có thể làm lỗi các tour đang thuộc danh mục đó. Bạn chắc chứ?\")' 
                                           class='btn btn-outline-danger btn-sm'>
                                            <i class='bi bi-trash'></i> Xóa
                                        </a>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>