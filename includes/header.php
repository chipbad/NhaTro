<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Nhà Trọ SV</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=search">Tìm phòng</a>
                    </li>
                    <?php if(isLoggedIn() && $_SESSION['role'] == 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=saved-rooms">
                                <i class="fas fa-heart"></i> Phòng đã lưu
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=profile">Xin chào, <?php echo $_SESSION['username']; ?></a>
                        </li>
                        <?php if(isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=admin">Quản trị</a>
                            </li>
                        <?php endif; ?>
                        <?php if(isLandlord()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=landlord-dashboard">Quản lý phòng trọ</a>
                            </li>
                        <?php endif; ?>
                        <?php if(isLoggedIn() && isLandlord()): ?>
                            <?php
                            // Đếm số lịch hẹn chờ duyệt
                            $stmt = $conn->prepare("
                                SELECT COUNT(*) FROM viewing_schedules 
                                WHERE landlord_id = ? AND status = 'pending'
                            ");
                            $stmt->execute([$_SESSION['user_id']]);
                            $pending_count = $stmt->fetchColumn();
                            ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?page=landlord-dashboard&action=schedules">
                                    <i class="fas fa-calendar-check"></i> Lịch hẹn
                                    <?php if ($pending_count > 0): ?>
                                        <span class="badge bg-danger"><?php echo $pending_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <?php
                            // Đếm tin nhắn chưa đọc
                            $stmt = $conn->prepare("
                                SELECT COUNT(*) FROM messages 
                                WHERE receiver_id = ? AND is_read = 0
                            ");
                            $stmt->execute([$_SESSION['user_id']]);
                            $unread = $stmt->fetchColumn();
                            ?>
                            <a class="nav-link" href="?page=chat">
                                <i class="fas fa-comments"></i> Tin nhắn
                                <?php if ($unread > 0): ?>
                                    <span class="badge bg-danger"><?php echo $unread; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=logout">Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=login">Đăng nhập</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?page=register">Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
