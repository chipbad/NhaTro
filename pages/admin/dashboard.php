<div class="container-fluid py-4">
    <div class="row dashboard-stats">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tổng số phòng trọ</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) FROM rooms");
                    $total_rooms = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-primary"><?php echo $total_rooms; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Phòng chờ duyệt</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) FROM rooms WHERE status = 'pending'");
                    $pending_rooms = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-warning"><?php echo $pending_rooms; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tổng người dùng</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) FROM users");
                    $total_users = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-success"><?php echo $total_users; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Đánh giá mới</h5>
                    <?php
                    $stmt = $conn->query("SELECT COUNT(*) FROM reviews WHERE DATE(created_at) = CURDATE()");
                    $today_reviews = $stmt->fetchColumn();
                    ?>
                    <h2 class="text-info"><?php echo $today_reviews; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ thống kê -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thống kê phòng trọ theo quận</h5>
                    <?php
                    $stmt = $conn->query("SELECT district, COUNT(*) as count 
                                        FROM rooms 
                                        GROUP BY district 
                                        ORDER BY count DESC");
                    $district_stats = $stmt->fetchAll();
                    ?>
                    <canvas id="districtChart" data-type="bar" 
                            data-chart-data='<?php echo json_encode([
                                'labels' => array_column($district_stats, 'district'),
                                'datasets' => [[
                                    'label' => 'Số lượng phòng trọ',
                                    'data' => array_column($district_stats, 'count'),
                                    'backgroundColor' => '#4e73df'
                                ]]
                            ]); ?>'></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Người dùng đăng ký mới (7 ngày qua)</h5>
                    <?php
                    $stmt = $conn->query("SELECT DATE(created_at) as date, COUNT(*) as count 
                                        FROM users 
                                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                        GROUP BY DATE(created_at)");
                    $user_stats = $stmt->fetchAll();
                    ?>
                    <canvas id="userChart" data-type="line" 
                            data-chart-data='<?php echo json_encode([
                                'labels' => array_column($user_stats, 'date'),
                                'datasets' => [[
                                    'label' => 'Người dùng mới',
                                    'data' => array_column($user_stats, 'count'),
                                    'borderColor' => '#1cc88a'
                                ]]
                            ]); ?>'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
