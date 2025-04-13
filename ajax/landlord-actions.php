<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

if (!isLoggedIn() || !isLandlord()) {
    die(json_encode(['status' => 'error', 'message' => 'Không có quyền truy cập']));
}

$db = new Database();
$conn = $db->getConnection();

$action = $_POST['action'] ?? '';
$room_id = (int)$_POST['room_id'] ?? 0;

try {
    switch ($action) {
        case 'mark_rented':
            $stmt = $conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = ? AND landlord_id = ?");
            $stmt->execute([$room_id, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật trạng thái phòng']);
            break;

        case 'mark_available':
            $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ? AND landlord_id = ?");
            $stmt->execute([$room_id, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Đã cập nhật trạng thái phòng']);
            break;

        case 'delete_room':
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("DELETE FROM amenities WHERE room_id = ?");
            $stmt->execute([$room_id]);
            
            $stmt = $conn->prepare("DELETE FROM reviews WHERE room_id = ?");
            $stmt->execute([$room_id]);
            
            $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ? AND landlord_id = ?");
            $stmt->execute([$room_id, $_SESSION['user_id']]);
            
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Đã xóa phòng trọ']);
            break;

        case 'reply_review':
            $review_id = (int)$_POST['review_id'];
            $reply = cleanInput($_POST['reply']);
            
            $stmt = $conn->prepare("UPDATE reviews SET reply = ? WHERE id = ? AND EXISTS (
                SELECT 1 FROM rooms WHERE id = reviews.room_id AND landlord_id = ?
            )");
            $stmt->execute([$reply, $review_id, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Đã gửi phản hồi']);
            break;

        case 'approve_schedule':
        case 'reject_schedule':
            $schedule_id = (int)$_POST['schedule_id'];
            $status = ($action === 'approve_schedule') ? 'approved' : 'rejected';
            $reason = isset($_POST['reason']) ? cleanInput($_POST['reason']) : '';
            
            try {
                $stmt = $conn->prepare("
                    UPDATE viewing_schedules 
                    SET status = ?, 
                        notes = CASE 
                            WHEN ? != '' THEN CONCAT(COALESCE(notes, ''), '\nLý do từ chối: ', ?)
                            ELSE notes 
                        END
                    WHERE id = ? AND landlord_id = ?
                ");
                
                if ($stmt->execute([$status, $reason, $reason, $schedule_id, $_SESSION['user_id']])) {
                    echo json_encode([
                        'status' => 'success',
                        'message' => ($status === 'approved') ? 'Đã duyệt lịch hẹn' : 'Đã từ chối lịch hẹn'
                    ]);
                } else {
                    throw new Exception("Không thể cập nhật trạng thái lịch hẹn");
                }
            } catch (Exception $e) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ]);
            }
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Có lỗi xảy ra']);
}
