<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    die(json_encode(['status' => 'error', 'message' => 'Không có quyền truy cập']));
}

$db = new Database();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$room_id = (int)$_POST['room_id'] ?? 0;

try {
    switch ($action) {
        case 'save_room':
            // Kiểm tra xem đã lưu chưa
            $stmt = $conn->prepare("SELECT id FROM saved_rooms WHERE user_id = ? AND room_id = ?");
            $stmt->execute([$_SESSION['user_id'], $room_id]);
            if ($stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'Phòng này đã được lưu']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO saved_rooms (user_id, room_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $room_id]);
            echo json_encode(['status' => 'success', 'message' => 'Đã lưu phòng trọ']);
            break;

        case 'unsave_room':
            $stmt = $conn->prepare("DELETE FROM saved_rooms WHERE user_id = ? AND room_id = ?");
            $stmt->execute([$_SESSION['user_id'], $room_id]);
            echo json_encode(['status' => 'success', 'message' => 'Đã bỏ lưu phòng trọ']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
