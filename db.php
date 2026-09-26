<?php
// 1. Vá lỗi Cookie No HttpOnly & SameSite (Chống trộm Session)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Đổi thành true nếu sau này dùng HTTPS
        'httponly' => true, // Chặn Javascript đọc Cookie
        'samesite' => 'Lax' // Chống giả mạo request xuyên trang (CSRF)
    ]);
    session_start();
}

// 2. Vá các lỗi thiếu Security Headers (Chống Clickjacking, XSS, MIME sniffing)
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// 3. Vá lỗi rò rỉ thông tin Server (Ẩn phiên bản PHP trên HTTP Response)
header_remove("X-Powered-By");

// 4. Kết nối Cơ sở dữ liệu MySQL (XAMPP)
$host = "localhost";
$user = "root";      
$pass = "";          
$dbname = "web_du_lich";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>