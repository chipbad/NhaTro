<?php
// Lấy danh sách tất cả phòng của chủ trọ
$stmt = $conn->prepare("SELECT r.*, COUNT(rv.id) as review_count, AVG(rv.rating) as avg_rating 
                       FROM rooms r 
                       LEFT JOIN reviews rv ON r.id = rv.room_id 
                       WHERE r.landlord_id = ? 
                       GROUP BY r.id 
                       ORDER BY r.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$rooms = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title">Danh sách phòng trọ của tôi</h5>
            <a href="?page=room-add" class="btn btn-success">
                <i class="fas fa-plus"></i> Thêm phòng mới
            </a>
        </div>

        <?php if (empty($rooms)): ?>
            <div class="alert alert-info">
                Bạn chưa có phòng trọ nào. Hãy thêm phòng mới!
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Giá (VNĐ)</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Đánh giá</th>
                            <th>Ngày đăng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['title']); ?></td>
                                <td><?php echo number_format($room['price']); ?></td>
                                <td><?php echo htmlspecialchars($room['address']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeColor($room['status']); ?>">
                                        <?php echo getStatusText($room['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($room['review_count'] > 0): ?>
                                        <span class="text-warning">
                                            <?php echo number_format($room['avg_rating'], 1); ?> ⭐
                                        </span>
                                        (<?php echo $room['review_count']; ?> đánh giá)
                                    <?php else: ?>
                                        Chưa có đánh giá
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($room['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?page=room-edit&id=<?php echo $room['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            Sửa
                                        </a>
                                        <?php if ($room['status'] == 'available'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success mark-rented" 
                                                    data-room-id="<?php echo $room['id']; ?>">
                                                Đã cho thuê
                                            </button>
                                        <?php elseif ($room['status'] == 'rented'): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-info mark-available" 
                                                    data-room-id="<?php echo $room['id']; ?>">
                                                Còn trống
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-room" 
                                                data-room-id="<?php echo $room['id']; ?>">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý đánh dấu đã cho thuê
    const markRentedButtons = document.querySelectorAll('.mark-rented');
    markRentedButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn đánh dấu phòng này đã cho thuê?')) {
                const roomId = this.dataset.roomId;
                // Thêm code xử lý AJAX ở đây
            }
        });
    });

    // Xử lý đánh dấu còn trống
    const markAvailableButtons = document.querySelectorAll('.mark-available');
    markAvailableButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn đánh dấu phòng này còn trống?')) {
                const roomId = this.dataset.roomId;
                // Thêm code xử lý AJAX ở đây
            }
        });
    });

    // Xử lý xóa phòng
    const deleteButtons = document.querySelectorAll('.delete-room');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa phòng này? Hành động này không thể hoàn tác!')) {
                const roomId = this.dataset.roomId;
                // Thêm code xử lý AJAX ở đây
            }
        });
    });
});
</script>
