<?php 
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';


if(isset($_POST['btnAddCat'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['cat_name']);
    if(!empty($cat_name)) {
        $sql = "INSERT INTO Categories (CategoryName) VALUES ('$cat_name')";
        if(mysqli_query($conn, $sql)) {
            echo "<script>alert('Đã thêm danh mục: $cat_name');</script>";
        }
    }
}


if(isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM Categories WHERE CategoryID = $id");
    header("Location: admin_categories.php");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý danh mục - MienTay Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table thead { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

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

</body>
</html>