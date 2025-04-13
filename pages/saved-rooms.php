<?php
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    redirect('index.php');
}

// Lấy danh sách phòng đã lưu
$stmt = $conn->prepare("
    SELECT r.*, u.username as landlord_name, u.phone as landlord_phone,
           (SELECT image_path FROM room_images WHERE room_id = r.id AND is_main = 1 LIMIT 1) as main_image
    FROM saved_rooms sr
    JOIN rooms r ON sr.room_id = r.id
    JOIN users u ON r.landlord_id = u.id
    WHERE sr.user_id = ?
    ORDER BY sr.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$saved_rooms = $stmt->fetchAll();
?>

<div class="container">
    <h2 class="mb-4">Phòng trọ đã lưu</h2>

    <?php if (empty($saved_rooms)): ?>
        <div class="alert alert-info">
            Bạn chưa lưu phòng trọ nào. 
            <a href="?page=search" class="alert-link">Tìm phòng ngay</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($saved_rooms as $room): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo !empty($room['main_image']) ? $room['main_image'] : 'assets/images/room-placeholder.jpg'; ?>" 
                             class="card-img-top" alt="Room image" style="height: 200px; object-fit: cover;">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($room['title']); ?></h5>
                            <p class="card-text">
                                <strong>Giá:</strong> <?php echo number_format($room['price']); ?> VNĐ/tháng<br>
                                <strong>Diện tích:</strong> <?php echo $room['area']; ?> m²<br>
                                <strong>Khu vực:</strong> <?php echo htmlspecialchars($room['district']); ?><br>
                                <strong>Chủ trọ:</strong> <?php echo htmlspecialchars($room['landlord_name']); ?>
                            </p>
                        </div>

                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex justify-content-between">
                                <a href="?page=room&id=<?php echo $room['id']; ?>" 
                                   class="btn btn-primary">Xem chi tiết</a>
                                <button class="btn btn-danger unsave-room" 
                                        data-room-id="<?php echo $room['id']; ?>">
                                    <i class="fas fa-heart-broken"></i> Bỏ lưu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý bỏ lưu phòng
    document.querySelectorAll('.unsave-room').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn bỏ lưu phòng này?')) {
                const roomId = this.dataset.roomId;
                
                fetch('/nha-troSV/ajax/student-actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=unsave_room&room_id=${roomId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Xóa card phòng trọ khỏi giao diện
                        this.closest('.col-md-4').remove();
                        // Kiểm tra nếu không còn phòng nào thì hiển thị thông báo
                        if (document.querySelectorAll('.col-md-4').length === 0) {
                            location.reload();
                        }
                    }
                    alert(data.message);
                })
                .catch(error => alert('Có lỗi xảy ra'));
            }
        });
    });
});
</script>
