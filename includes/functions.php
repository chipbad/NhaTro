<?php
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLandlord() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'landlord';
}

function redirect($path) {
    header("Location: $path");
    exit();
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

function getStatusBadgeColor($status) {
    switch($status) {
        case 'available':
            return 'success';
        case 'rented':
            return 'primary';
        case 'pending':
            return 'warning';
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getStatusText($status) {
    switch($status) {
        case 'available':
            return 'Còn trống';
        case 'rented':
            return 'Đã cho thuê';
        case 'pending':
            return 'Chờ duyệt';
        case 'rejected':
            return 'Đã từ chối';
        default:
            return 'Không xác định';
    }
}

// Thêm các hàm helper cho lịch hẹn
function getScheduleStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function getScheduleStatusText($status) {
    switch ($status) {
        case 'pending': return 'Chờ duyệt';
        case 'approved': return 'Đã duyệt';
        case 'rejected': return 'Từ chối';
        default: return 'Không xác định';
    }
}

function getHanoiDistricts() {
    return [
        'Ba Dinh' => 'Ba Đình',
        'Hoan Kiem' => 'Hoàn Kiếm',
        'Hai Ba Trung' => 'Hai Bà Trưng',
        'Dong Da' => 'Đống Đa',
        'Tay Ho' => 'Tây Hồ',
        'Cau Giay' => 'Cầu Giấy',
        'Thanh Xuan' => 'Thanh Xuân',
        'Hoang Mai' => 'Hoàng Mai',
        'Long Bien' => 'Long Biên',
        'Nam Tu Liem' => 'Nam Từ Liêm',
        'Bac Tu Liem' => 'Bắc Từ Liêm',
        'Ha Dong' => 'Hà Đông',
        'Son Tay' => 'Sơn Tây',
        'My Duc' => 'Mỹ Đức',
        'Ung Hoa' => 'Ứng Hòa',
        'Thuong Tin' => 'Thường Tín',
        'Phu Xuyen' => 'Phú Xuyên',
        'Me Linh' => 'Mê Linh',
        'Soc Son' => 'Sóc Sơn',
        'Ba Vi' => 'Ba Vì',
        'Thach That' => 'Thạch Thất',
        'Quoc Oai' => 'Quốc Oai',
        'Dan Phuong' => 'Đan Phượng',
        'Hoai Duc' => 'Hoài Đức',
        'Thanh Oai' => 'Thanh Oai',
        'Thanh Tri' => 'Thanh Trì',
        'Gia Lam' => 'Gia Lâm',
        'Dong Anh' => 'Đông Anh',
        'Chuong My' => 'Chương Mỹ',
        'Phuc Tho' => 'Phúc Thọ'
    ];
}
?>
