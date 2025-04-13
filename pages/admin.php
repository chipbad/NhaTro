<?php
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch($action) {
        case 'approve_room':
            $room_id = (int)$_POST['room_id'];
            $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
            $stmt->execute([$room_id]);
            break;
            
        case 'block_user':
            $user_id = (int)$_POST['user_id'];
            $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
            $stmt->execute([$user_id]);
            break;
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action == 'dashboard' ? 'active' : ''; ?>" 
                           href="?page=admin&action=dashboard">
                            Tổng quan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action == 'rooms' ? 'active' : ''; ?>" 
                           href="?page=admin&action=rooms">
                            Quản lý phòng trọ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $action == 'users' ? 'active' : ''; ?>" 
                           href="?page=admin&action=users">
                            Quản lý người dùng
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-10 ms-sm-auto px-md-4">
            <?php
            switch($action) {
                case 'dashboard':
                    include 'admin/dashboard.php';
                    break;
                case 'rooms':
                    include 'admin/rooms.php';
                    break;
                case 'users':
                    include 'admin/users.php';
                    break;
                case 'edit-room':
                    include 'admin/edit-room.php';
                    break;
            }
            ?>
        </main>
    </div>
</div>
