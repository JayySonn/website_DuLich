<?php 
session_start();
include 'db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); 
    exit();
}

// --- 1. TẠO MÃ BẢO MẬT CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- 2. XỬ LÝ POST: ĐÁNH DẤU ĐÃ ĐỌC & XÓA TIN NHẮN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("<script>alert('Lỗi bảo mật!'); window.location.href='admin_contacts.php';</script>");
    }

    if (isset($_POST['action']) && isset($_POST['contact_id'])) {
        $id = intval($_POST['contact_id']);

        if ($_POST['action'] === 'mark_done') {
            // Cập nhật trạng thái thành Đã xử lý (1)
            $sql_update = "UPDATE contacts SET Status = 1 WHERE ContactID = ?";
            $stmt = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        } 
        elseif ($_POST['action'] === 'delete') {
            // Xóa tin nhắn
            $sql_delete = "DELETE FROM contacts WHERE ContactID = ?";
            $stmt = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }
        
        header("Location: admin_contacts.php");
        exit();
    }
}

// --- 3. LẤY DANH SÁCH LIÊN HỆ TỪ MỚI ĐẾN CŨ ---
$sql_contacts = "SELECT * FROM contacts ORDER BY CreatedAt DESC";
$res_contacts = mysqli_query($conn, $sql_contacts);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hộp thư Liên hệ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>

<?php include 'navbar_admin.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="bi bi-inbox text-info me-2"></i> Hộp Thư Liên Hệ</h2>
        <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <div class="table-container">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th width="5%">STT</th>
                    <th width="25%">Người gửi</th>
                    <th width="35%">Nội dung tóm tắt</th>
                    <th width="15%">Ngày gửi</th>
                    <th width="10%">Trạng thái</th>
                    <th width="10%" class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stt = 1;
                if (mysqli_num_rows($res_contacts) > 0) {
                    while($row = mysqli_fetch_assoc($res_contacts)) {
                        $is_read = $row['Status'] == 1;
                        $row_class = $is_read ? "text-muted" : "fw-bold";
                        ?>
                        <tr class="<?php echo $is_read ? '' : 'table-info bg-opacity-10'; ?>">
                            <td class="text-muted">#<?php echo $stt++; ?></td>
                            <td>
                                <div class="<?php echo $row_class; ?>"><?php echo htmlspecialchars($row['FullName']); ?></div>
                                <small class="text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($row['Email']); ?></small><br>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($row['Phone']); ?></small>
                            </td>
                            <td>
                                <div class="text-truncate-2 <?php echo $row_class; ?>" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($row['Message']); ?>
                                </div>
                            </td>
                            <td class="small text-muted">
                                <?php echo date('H:i d/m/Y', strtotime($row['CreatedAt'])); ?>
                            </td>
                            <td>
                                <?php if($is_read): ?>
                                    <span class="badge bg-success"><i class="bi bi-check2-all"></i> Đã xử lý</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-envelope-exclamation"></i> Mới</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Nút Xem chi tiết mở Modal -->
                                    <button class="btn btn-sm btn-outline-primary view-contact" 
                                        data-id="<?php echo $row['ContactID']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['FullName']); ?>"
                                        data-email="<?php echo htmlspecialchars($row['Email']); ?>"
                                        data-phone="<?php echo htmlspecialchars($row['Phone']); ?>"
                                        data-date="<?php echo date('H:i d/m/Y', strtotime($row['CreatedAt'])); ?>"
                                        data-message="<?php echo htmlspecialchars($row['Message']); ?>"
                                        title="Đọc tin nhắn">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Nút thao tác (Dùng Form ẩn chống hack) -->
                                    <form method="POST" action="admin_contacts.php" class="d-inline mb-0">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="contact_id" value="<?php echo $row['ContactID']; ?>">
                                        
                                        <?php if(!$is_read): ?>
                                            <button type="submit" name="action" value="mark_done" class="btn btn-sm btn-success" title="Đánh dấu đã xử lý">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc muốn xóa tin nhắn này?');" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-5 text-muted fst-italic'>Hộp thư hiện đang trống.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL ĐỌC TIN NHẮN -->
<div class="modal fade" id="modalContact" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-envelope-open me-2"></i> Nội dung lời nhắn</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3 pb-3 border-bottom">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Người gửi:</p>
                        <h6 class="fw-bold mb-0" id="mc-name"></h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1 text-muted small">Ngày gửi:</p>
                        <h6 class="fw-bold mb-0" id="mc-date"></h6>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <i class="bi bi-telephone text-primary"></i> <span id="mc-phone"></span>
                    </div>
                    <div class="col-md-6">
                        <i class="bi bi-envelope text-primary"></i> <span id="mc-email"></span>
                    </div>
                </div>
                <div class="bg-light p-3 rounded" style="min-height: 150px; white-space: pre-wrap;" id="mc-message">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Đổ dữ liệu vào Modal khi bấm nút Xem
document.querySelectorAll('.view-contact').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('mc-name').innerText = this.dataset.name;
        document.getElementById('mc-email').innerText = this.dataset.email;
        document.getElementById('mc-phone').innerText = this.dataset.phone;
        document.getElementById('mc-date').innerText = this.dataset.date;
        document.getElementById('mc-message').innerText = this.dataset.message;
        
        new bootstrap.Modal(document.getElementById('modalContact')).show();
    });
});
</script>
</body>
</html>