<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();
$conn = $db->getConnection();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà trọ Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <?php if (isset($_GET['page']) && $_GET['page'] == 'admin'): ?>
    <link href="assets/css/admin.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container mt-4">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        $allowed_pages = [
            'home', 'search', 'room', 'login', 'register', 'profile', 
            'admin', 'logout', 'room-add', 'room-edit', 'landlord-dashboard',
            'saved-rooms', 'chat'  // Thêm trang chat
        ];
        
        if (in_array($page, $allowed_pages)) {
            include "pages/{$page}.php";
        } else {
            include "pages/404.php";
        }
        ?>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <?php if (isset($_GET['page']) && $_GET['page'] == 'admin'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/admin.js"></script>
    <?php endif; ?>
</body>
</html>
