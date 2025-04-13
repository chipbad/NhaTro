<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    die(json_encode(['status' => 'error', 'message' => 'Không có quyền truy cập']));
}

$db = new Database();
$conn = $db->getConnection();

$room_id = (int)$_POST['room_id'];
$landlord_id = (int)$_POST['landlord_id'];
$viewing_date = $_POST['viewing_date'];
$viewing_time = $_POST['viewing_time'];
$notes = cleanInput($_POST['notes']);

try {
    // Kiểm tra xem đã có lịch hẹn trong khung giờ này chưa
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM viewing_schedules 
        WHERE room_id = ? AND viewing_date = ? AND viewing_time = ? 
        AND status != 'rejected'
    ");
    $stmt->execute([$room_id, $viewing_date, $viewing_time]);
    if ($stmt->fetchColumn() > 0) {
        die(json_encode([
            'status' => 'error',
            'message' => 'Khung giờ này đã có người đặt lịch'
        ]));
    }

    // Thêm lịch hẹn mới
    $stmt = $conn->prepare("
        INSERT INTO viewing_schedules (room_id, student_id, landlord_id, viewing_date, viewing_time, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $room_id,
        $_SESSION['user_id'],
        $landlord_id,
        $viewing_date,
        $viewing_time,
        $notes
    ]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra']);
}
