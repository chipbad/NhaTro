<?php
// Phân tích chi tiết về hiệu suất phòng trọ
$stmt = $conn->prepare("
    SELECT 
        r.id,
        r.title,
        r.price,
        COUNT(DISTINCT rv.id) as total_views,
        COUNT(DISTINCT sr.id) as total_saves,
        COUNT(DISTINCT rev.id) as total_reviews,
        AVG(rev.rating) as avg_rating
    FROM rooms r
    LEFT JOIN room_views rv ON r.id = rv.room_id
    LEFT JOIN saved_rooms sr ON r.id = sr.room_id
    LEFT JOIN reviews rev ON r.id = rev.room_id
    WHERE r.landlord_id = ?
    GROUP BY r.id
");
$stmt->execute([$_SESSION['user_id']]);
$room_stats = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5>Phân tích hiệu suất phòng trọ</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Giá</th>
                                <th>Lượt xem</th>
                                <th>Lượt lưu</th>
                                <th>Đánh giá</th>
                                <th>Tỷ lệ chuyển đổi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($room_stats as $stat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stat['title']); ?></td>
                                    <td><?php echo number_format($stat['price']); ?> VNĐ</td>
                                    <td><?php echo $stat['total_views']; ?></td>
                                    <td><?php echo $stat['total_saves']; ?></td>
                                    <td>
                                        <?php if ($stat['avg_rating']): ?>
                                            <?php echo number_format($stat['avg_rating'], 1); ?> ⭐
                                            (<?php echo $stat['total_reviews']; ?>)
                                        <?php else: ?>
                                            Chưa có đánh giá
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($stat['total_views'] > 0) {
                                            $conversion = ($stat['total_saves'] / $stat['total_views']) * 100;
                                            echo number_format($conversion, 1) . '%';
                                        } else {
                                            echo '0%';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
