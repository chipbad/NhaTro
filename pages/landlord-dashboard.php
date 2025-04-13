<?php
if (!isLoggedIn() || !isLandlord()) {
    redirect('index.php');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'overview';
?>

<!-- Thêm vào phần head của trang -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="assets/js/landlord.js"></script>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="list-group">
                <a href="?page=landlord-dashboard&action=overview" 
                   class="list-group-item <?php echo $action == 'overview' ? 'active' : ''; ?>">
                    Tổng quan
                </a>
                <a href="?page=room-add" 
                   class="list-group-item">
                    Đăng phòng mới
                </a>
                <a href="?page=landlord-dashboard&action=rooms" 
                   class="list-group-item <?php echo $action == 'rooms' ? 'active' : ''; ?>">
                    Danh sách phòng
                </a>
                <a href="?page=landlord-dashboard&action=reviews" 
                   class="list-group-item <?php echo $action == 'reviews' ? 'active' : ''; ?>">
                    Đánh giá
                </a>
                <a href="?page=landlord-dashboard&action=statistics" 
                   class="list-group-item <?php echo $action == 'statistics' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Thống kê
                </a>
                <a href="?page=landlord-dashboard&action=settings" 
                   class="list-group-item <?php echo $action == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Cài đặt
                </a>
                <a href="?page=landlord-dashboard&action=schedules" 
                   class="list-group-item <?php echo $action == 'schedules' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Lịch hẹn
                    <?php if (isset($total_pending) && $total_pending > 0): ?>
                        <span class="badge bg-danger"><?php echo $total_pending; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9">
            <?php
            switch($action) {
                case 'overview':
                    include 'landlord/overview.php';
                    break;
                case 'rooms':
                    include 'landlord/rooms.php';
                    break;
                case 'reviews':
                    include 'landlord/reviews.php';
                    break;
                case 'statistics':
                    include 'landlord/statistics.php';
                    break;
                case 'settings':
                    include 'landlord/settings.php';
                    break;
                case 'schedules':
                    include 'landlord/schedules.php';
                    break;
                default:
                    include 'landlord/overview.php';
            }
            ?>
        </div>
    </div>
</div>
