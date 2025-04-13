<?php
// Cho phép sinh viên lưu khu vực yêu thích và nhận thông báo khi có phòng mới
$stmt = $conn->prepare("
    SELECT fa.*, COUNT(r.id) as new_rooms
    FROM favorite_areas fa
    LEFT JOIN rooms r ON fa.district = r.district 
        AND r.created_at > fa.last_notification
    WHERE fa.user_id = ?
    GROUP BY fa.district
");
$stmt->execute([$_SESSION['user_id']]);
$favorite_areas = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-body">
        <h5>Khu vực quan tâm</h5>
        <form method="POST" class="mb-3">
            <select name="district" class="form-control mb-2" required>
                <?php foreach (getHanoiDistricts() as $value => $label): ?>
                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Thêm khu vực</button>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Khu vực</th>
                        <th>Phòng mới</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($favorite_areas as $area): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($area['district']); ?></td>
                            <td>
                                <?php if ($area['new_rooms'] > 0): ?>
                                    <span class="badge bg-success"><?php echo $area['new_rooms']; ?> phòng mới</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger remove-area" 
                                        data-district="<?php echo $area['district']; ?>">
                                    Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
