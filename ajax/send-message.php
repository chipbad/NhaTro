<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn()) {
    die(json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']));
}

$db = new Database();
$conn = $db->getConnection();

$receiver_id = (int)$_POST['receiver_id'];
$room_id = (int)$_POST['room_id'];
$message = cleanInput($_POST['message']);

if (empty($message)) {
    die(json_encode(['status' => 'error', 'message' => 'Tin nhắn không được để trống']));
}

try {
    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, receiver_id, room_id, message) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $room_id, $message]);
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra']);
}
