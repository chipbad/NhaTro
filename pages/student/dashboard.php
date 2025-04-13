<?php
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    redirect('index.php');
}

// Lấy danh sách phòng đã lưu
$stmt = $conn->prepare("
    SELECT r.*, u.username as landlord_name 
    FROM saved_rooms sr
    JOIN rooms r ON sr.room_id = r.id
    JOIN users u ON r.landlord_id = u.id
    WHERE sr.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$saved_rooms = $stmt->fetchAll();

// Lấy lịch sử đánh giá
$stmt = $conn->prepare("
    SELECT r.*, rm.title as room_title
    FROM reviews r
    JOIN rooms rm ON r.room_id = rm.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();
?>

<div class="container">
    <div class="row">
        <!-- Phòng trọ đã lưu -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Phòng trọ đã lưu</h5>
                    <?php if ($saved_rooms): ?>
                        <div class="row">
                            <?php foreach ($saved_rooms as $room): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h6><?php echo htmlspecialchars($room['title']); ?></h6>
                                            <p class="text-muted">
                                                Giá: <?php echo number_format($room['price']); ?> VNĐ/tháng<br>
                                                Khu vực: <?php echo htmlspecialchars($room['district']); ?>
                                            </p>
                                            <div class="d-flex justify-content-between">
                                                <a href="?page=room&id=<?php echo $room['id']; ?>" class="btn btn-sm btn-primary">Xem chi tiết</a>
                                                <button class="btn btn-sm btn-danger remove-saved" data-room-id="<?php echo $room['id']; ?>">Bỏ lưu</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Bạn chưa lưu phòng trọ nào.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lịch sử đánh giá -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Đánh giá của bạn</h5>
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="border-bottom py-3">
                                <h6><?php echo htmlspecialchars($review['room_title']); ?></h6>
                                <div class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php echo $i <= $review['rating'] ? '⭐' : '☆'; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="mt-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <small class="text-muted">
                                    Đánh giá vào: <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Bạn chưa có đánh giá nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Thông tin cá nhân -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thông tin cá nhân</h5>
                    <form method="POST" action="?page=update-profile">
                        <!-- Form cập nhật thông tin -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
