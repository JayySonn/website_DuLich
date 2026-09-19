<?php
session_start();
include 'db.php';

if(isset($_POST['btnReview'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login_user.php");
        exit();
    }

    // Ép kiểu dữ liệu về số nguyên (int) để an toàn tuyệt đối
    $tour_id = intval($_POST['tour_id']);
    $user_id = intval($_SESSION['user_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // NÂNG CẤP BẢO MẬT: Dùng Prepared Statement và gán mặc định Status = 0 (Chờ duyệt)
    $sql = "INSERT INTO reviews (TourID, UserID, Rating, Comment, Status, ReviewDate) 
            VALUES (?, ?, ?, ?, 0, NOW())";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiis", $tour_id, $user_id, $rating, $comment);

    if(mysqli_stmt_execute($stmt)) {
        // Gắn thêm tham số msg=review_sent để trang detail.php có thể nhận diện và báo thành công
        header("Location: detail.php?id=" . $tour_id . "&msg=review_sent");
        exit();
    } else {
        // Ẩn lỗi SQL thực tế, chỉ báo lỗi chung chung ra màn hình cho người dùng
        echo "<script>alert('Hệ thống đang bận, vui lòng thử lại sau!'); window.history.back();</script>";
        exit();
    }
}
?>