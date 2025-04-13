<?php
// Thống kê và báo cáo chi tiết
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_rooms = $conn->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_reviews = $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

// Thống kê theo tháng
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as new_users,
        SUM(role = 'landlord') as new_landlords,
        SUM(role = 'student') as new_students
    FROM users
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
");
$stmt->execute();
$monthly_stats = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h5>Báo cáo tổng quan</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <h2><?php echo number_format($total_users); ?></h2>
                        <p>Người dùng</p>
                    </div>
                    <div class="col-md-4">
                        <h2><?php echo number_format($total_rooms); ?></h2>
                        <p>Phòng trọ</p>
                    </div>
                    <div class="col-md-4">
                        <h2><?php echo number_format($total_reviews); ?></h2>
                        <p>Đánh giá</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5>Thống kê người dùng theo tháng</h5>
                <canvas id="userStats"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Khởi tạo biểu đồ thống kê
const ctx = document.getElementById('userStats').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
        datasets: [
            {
                label: 'Chủ trọ mới',
                data: <?php echo json_encode(array_column($monthly_stats, 'new_landlords')); ?>,
                backgroundColor: '#4e73df'
            },
            {
                label: 'Sinh viên mới',
                data: <?php echo json_encode(array_column($monthly_stats, 'new_students')); ?>,
                backgroundColor: '#1cc88a'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
