<?php
// Remove duplicate function declarations since they're already in functions.php

// Lấy thống kê phòng trọ
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_rooms,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_rooms,
    SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) as rented_rooms,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_rooms
    FROM rooms WHERE landlord_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Lấy phòng mới nhất
$stmt = $conn->prepare("SELECT * FROM rooms WHERE landlord_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_rooms = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Tổng số phòng</h5>
                <h2><?php echo $stats['total_rooms']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Phòng đang cho thuê</h5>
                <h2><?php echo $stats['available_rooms']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning">
            <div class="card-body">
                <h5>Phòng chờ duyệt</h5>
                <h2><?php echo $stats['pending_rooms']; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Phòng trọ mới đăng</h5>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_rooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['title']); ?></td>
                            <td><?php echo number_format($room['price']); ?> VNĐ</td>
                            <td>
                                <span class="badge bg-<?php echo getStatusBadgeColor($room['status']); ?>">
                                    <?php echo getStatusText($room['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($room['created_at'])); ?></td>
                            <td>
                                <a href="?page=room-edit&id=<?php echo $room['id']; ?>" 
                                   class="btn btn-sm btn-primary">Sửa</a>
                                <?php if ($room['status'] == 'available'): ?>
                                    <button class="btn btn-sm btn-success mark-rented" 
                                            data-room-id="<?php echo $room['id']; ?>">
                                        Đánh dấu đã thuê
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
