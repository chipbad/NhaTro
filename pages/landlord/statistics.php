<?php
// Thống kê theo tháng
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as view_count
    FROM room_views 
    WHERE room_id IN (SELECT id FROM rooms WHERE landlord_id = ?)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$stmt->execute([$_SESSION['user_id']]);
$monthly_views = $stmt->fetchAll();

// Thống kê đánh giá
$stmt = $conn->prepare("
    SELECT 
        rating,
        COUNT(*) as count
    FROM reviews r
    JOIN rooms rm ON r.room_id = rm.id
    WHERE rm.landlord_id = ?
    GROUP BY rating
");
$stmt->execute([$_SESSION['user_id']]);
$ratings = $stmt->fetchAll();

// Thống kê phòng theo khu vực
$stmt = $conn->prepare("
    SELECT 
        district,
        COUNT(*) as room_count,
        AVG(price) as avg_price
    FROM rooms
    WHERE landlord_id = ?
    GROUP BY district
");
$stmt->execute([$_SESSION['user_id']]);
$district_stats = $stmt->fetchAll();
?>

<div class="row">
    <!-- Biểu đồ lượt xem theo tháng -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Lượt xem theo tháng</h5>
                <canvas id="viewsChart" data-type="line" 
                        data-chart-data='<?php echo json_encode([
                            'labels' => array_column($monthly_views, 'month'),
                            'datasets' => [[
                                'label' => 'Lượt xem',
                                'data' => array_column($monthly_views, 'view_count'),
                                'borderColor' => '#007bff',
                                'fill' => false
                            ]]
                        ]); ?>'></canvas>
            </div>
        </div>
    </div>

    <!-- Biểu đồ phân bố đánh giá -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Phân bố đánh giá</h5>
                <canvas id="ratingsChart" data-type="bar" 
                        data-chart-data='<?php echo json_encode([
                            'labels' => ['1 sao', '2 sao', '3 sao', '4 sao', '5 sao'],
                            'datasets' => [[
                                'label' => 'Số lượng đánh giá',
                                'data' => array_column($ratings, 'count'),
                                'backgroundColor' => '#28a745'
                            ]]
                        ]); ?>'></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Thống kê theo khu vực -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Thống kê theo khu vực</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Khu vực</th>
                                <th>Số phòng</th>
                                <th>Giá trung bình</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($district_stats as $stat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stat['district']); ?></td>
                                    <td><?php echo $stat['room_count']; ?></td>
                                    <td><?php echo number_format($stat['avg_price']); ?> VNĐ</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo ($stat['room_count'] / array_sum(array_column($district_stats, 'room_count'))) * 100; ?>%">
                                            </div>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo biểu đồ
    const charts = document.querySelectorAll('canvas[data-chart-data]');
    charts.forEach(canvas => {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: canvas.dataset.type,
            data: JSON.parse(canvas.dataset.chartData),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
});
</script>
