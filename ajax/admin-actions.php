<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn() || !isAdmin()) {
    die(json_encode(['status' => 'error', 'message' => 'Không có quyền truy cập']));
}

$db = new Database();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$room_id = (int)$_POST['room_id'] ?? 0;

try {
    switch ($action) {
        case 'approve_room':
            $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
            if ($stmt->execute([$room_id])) {
                // Gửi email thông báo cho chủ trọ
                $stmt = $conn->prepare("
                    SELECT r.title, u.email, u.username 
                    FROM rooms r 
                    JOIN users u ON r.landlord_id = u.id 
                    WHERE r.id = ?
                ");
                $stmt->execute([$room_id]);
                $room = $stmt->fetch();
                
                // TODO: Thêm code gửi email thông báo ở đây
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Đã duyệt phòng trọ thành công'
                ]);
            } else {
                throw new Exception("Không thể duyệt phòng");
            }
            break;

        case 'reject_room':
            $reason = $_POST['reason'] ?? '';
            $stmt = $conn->prepare("UPDATE rooms SET status = 'rejected' WHERE id = ?");
            if ($stmt->execute([$room_id])) {
                // Gửi email thông báo lý do từ chối cho chủ trọ
                $stmt = $conn->prepare("
                    SELECT r.title, u.email, u.username 
                    FROM rooms r 
                    JOIN users u ON r.landlord_id = u.id 
                    WHERE r.id = ?
                ");
                $stmt->execute([$room_id]);
                $room = $stmt->fetch();
                
                // TODO: Thêm code gửi email thông báo ở đây
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Đã từ chối phòng trọ'
                ]);
            } else {
                throw new Exception("Không thể từ chối phòng");
            }
            break;

        default:
            echo json_encode([
                'status' => 'error', 
                'message' => 'Hành động không hợp lệ'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}
