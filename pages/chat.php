<?php
if (!isLoggedIn()) {
    redirect('?page=login');
}

$receiver_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$room_id = isset($_GET['room']) ? (int)$_GET['room'] : 0;

// Lấy danh sách chat
$stmt = $conn->prepare("
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id 
        END as other_user_id,
        u.username,
        r.id as room_id,
        r.title as room_title,
        (SELECT message FROM messages 
         WHERE (sender_id = ? AND receiver_id = other_user_id) 
         OR (sender_id = other_user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = ? AND receiver_id = other_user_id) 
         OR (sender_id = other_user_id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_time
    FROM messages m
    JOIN users u ON u.id = CASE 
        WHEN m.sender_id = ? THEN m.receiver_id
        ELSE m.sender_id 
    END
    LEFT JOIN rooms r ON m.room_id = r.id
    WHERE ? IN (sender_id, receiver_id)
    ORDER BY last_time DESC
");
$stmt->execute([
    $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'],
    $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'],
    $_SESSION['user_id']
]);
$conversations = $stmt->fetchAll();

// Lấy tin nhắn của cuộc trò chuyện hiện tại
if ($receiver_id) {
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.role 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
        OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']]);
    $messages = array_reverse($stmt->fetchAll());

    // Đánh dấu là đã đọc
    $stmt = $conn->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $stmt->execute([$receiver_id, $_SESSION['user_id']]);
}
?>

<div class="container py-4">
    <div class="row">
        <!-- Danh sách chat -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tin nhắn</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($conversations as $chat): ?>
                        <a href="?page=chat&user=<?php echo $chat['other_user_id']; ?><?php echo $chat['room_id'] ? '&room='.$chat['room_id'] : ''; ?>" 
                           class="list-group-item list-group-item-action <?php echo $chat['other_user_id'] == $receiver_id ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-1"><?php echo htmlspecialchars($chat['username']); ?></h6>
                                <small><?php echo date('H:i', strtotime($chat['last_time'])); ?></small>
                            </div>
                            <?php if ($chat['room_title']): ?>
                                <small class="text-muted">Về phòng: <?php echo htmlspecialchars($chat['room_title']); ?></small>
                            <?php endif; ?>
                            <p class="mb-1 text-truncate"><?php echo htmlspecialchars($chat['last_message']); ?></p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Khung chat -->
        <div class="col-md-8">
            <?php if ($receiver_id): ?>
                <div class="card">
                    <div class="card-body" style="height: 500px;">
                        <div class="chat-messages p-4" style="height: 400px; overflow-y: auto;">
                            <?php foreach ($messages as $message): ?>
                                <div class="message mb-3 <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'text-end' : ''; ?>">
                                    <div class="d-inline-block">
                                        <div class="px-3 py-2 rounded <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'bg-primary text-white' : 'bg-light'; ?>">
                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <form id="chatForm" class="mt-3">
                            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                            <div class="input-group">
                                <textarea class="form-control" name="message" rows="1" 
                                          placeholder="Nhập tin nhắn..." required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-comments fa-3x mb-3"></i>
                    <p>Chọn một cuộc trò chuyện để bắt đầu</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.querySelector('.chat-messages');
    const chatForm = document.getElementById('chatForm');

    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('ajax/send-message.php', {  // Cập nhật đường dẫn này
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    chatForm.reset();
                    location.reload(); // Tạm thời reload, có thể cải thiện bằng cách thêm tin nhắn vào DOM
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => alert('Có lỗi xảy ra'));
        });
    }
});
</script>
