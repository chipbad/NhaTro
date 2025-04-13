<?php
if (!isLoggedIn() || !isLandlord()) {
    redirect('index.php');
}

// Lấy thống kê
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_rooms,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as active_rooms,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_rooms
    FROM rooms WHERE landlord_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Lấy thông báo đặt lịch mới
$stmt = $conn->prepare("
    SELECT vs.*, r.title as room_title, u.username as student_name, u.phone as student_phone
    FROM viewing_schedules vs
    JOIN rooms r ON vs.room_id = r.id
    JOIN users u ON vs.student_id = u.id
    WHERE vs.landlord_id = ? AND vs.status = 'pending'
    ORDER BY vs.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$pending_schedules = $stmt->fetchAll();

// Đếm tổng số lịch hẹn chờ duyệt
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM viewing_schedules 
    WHERE landlord_id = ? AND status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$total_pending = $stmt->fetchColumn();

// Lấy danh sách phòng mới nhất
$stmt = $conn->prepare("SELECT * FROM rooms WHERE landlord_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_rooms = $stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Tổng số phòng</h5>
                    <h2><?php echo $stats['total_rooms']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Phòng đang cho thuê</h5>
                    <h2><?php echo $stats['active_rooms']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Phòng chờ duyệt</h5>
                    <h2><?php echo $stats['pending_rooms']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <?php if ($total_pending > 0): ?>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Lịch hẹn xem phòng mới (<?php echo $total_pending; ?>)</h5>
                    <a href="?page=landlord-dashboard&action=schedules" class="btn btn-primary btn-sm">
                        Xem tất cả
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Người xem</th>
                                <th>Thời gian</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($schedule['room_title']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($schedule['student_name']); ?>
                                        <br>
                                        <small><?php echo $schedule['student_phone']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($schedule['viewing_date'])); ?>
                                        <br>
                                        <?php echo date('H:i', strtotime($schedule['viewing_time'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success approve-schedule" 
                                                    data-id="<?php echo $schedule['id']; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger reject-schedule" 
                                                    data-id="<?php echo $schedule['id']; ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mt-4">
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
                                <td><?php echo $room['status']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($room['created_at'])); ?></td>
                                <td>
                                    <a href="?page=room-edit&id=<?php echo $room['id']; ?>" 
                                       class="btn btn-sm btn-primary">Sửa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
