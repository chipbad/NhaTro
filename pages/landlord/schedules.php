<?php
// Lấy danh sách lịch hẹn
$stmt = $conn->prepare("
    SELECT vs.*, r.title as room_title, u.username as student_name, u.phone as student_phone
    FROM viewing_schedules vs
    JOIN rooms r ON vs.room_id = r.id
    JOIN users u ON vs.student_id = u.id
    WHERE vs.landlord_id = ?
    ORDER BY vs.viewing_date ASC, vs.viewing_time ASC
");
$stmt->execute([$_SESSION['user_id']]);
$schedules = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Lịch hẹn xem phòng</h5>
        
        <?php if (empty($schedules)): ?>
            <p class="text-muted">Chưa có lịch hẹn nào.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Phòng</th>
                            <th>Người xem</th>
                            <th>Thời gian</th>
                            <th>Ghi chú</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
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
                                <td><?php echo nl2br(htmlspecialchars($schedule['notes'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getScheduleStatusColor($schedule['status']); ?>">
                                        <?php echo getScheduleStatusText($schedule['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($schedule['status'] == 'pending'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success approve-schedule" 
                                                    data-id="<?php echo $schedule['id']; ?>">
                                                Duyệt
                                            </button>
                                            <button class="btn btn-danger reject-schedule" 
                                                    data-id="<?php echo $schedule['id']; ?>">
                                                Từ chối
                                            </button>
                                        </div>
                                    <?php endif; ?>
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
// Xử lý duyệt/từ chối lịch hẹn
document.querySelectorAll('.approve-schedule, .reject-schedule').forEach(button => {
    button.addEventListener('click', function() {
        const action = this.classList.contains('approve-schedule') ? 'approve_schedule' : 'reject_schedule';
        const id = this.dataset.id;
        
        let reason = '';
        if (action === 'reject_schedule') {
            reason = prompt('Lý do từ chối:');
            if (reason === null) return;
        }
        
        fetch('/nha-troSV/ajax/landlord-actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}&schedule_id=${id}&reason=${encodeURIComponent(reason || '')}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xử lý yêu cầu');
        });
    });
});
</script>
