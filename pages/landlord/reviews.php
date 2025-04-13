<?php
$stmt = $conn->prepare("
    SELECT r.*, rm.title as room_title, u.username as reviewer_name 
    FROM reviews r 
    JOIN rooms rm ON r.room_id = rm.id 
    JOIN users u ON r.user_id = u.id 
    WHERE rm.landlord_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Đánh giá từ người thuê</h5>
        
        <?php if (empty($reviews)): ?>
            <div class="alert alert-info">Chưa có đánh giá nào.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars($review['room_title']); ?></h6>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-warning me-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php echo $i <= $review['rating'] ? '⭐' : '☆'; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <small class="text-muted">
                                        bởi <?php echo htmlspecialchars($review['reviewer_name']); ?>
                                    </small>
                                </div>
                                
                                <p class="review-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <small class="text-muted d-block mb-3">
                                    <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                                </small>

                                <?php if ($review['reply']): ?>
                                    <div class="reply-section bg-light p-3 rounded">
                                        <small class="text-muted">Phản hồi của bạn:</small>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['reply'])); ?></p>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary mt-2 edit-reply" 
                                            data-review-id="<?php echo $review['id']; ?>"
                                            data-reply="<?php echo htmlspecialchars($review['reply']); ?>">
                                        Sửa phản hồi
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-primary reply-button" 
                                            data-review-id="<?php echo $review['id']; ?>">
                                        Phản hồi
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal phản hồi -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Phản hồi đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="replyForm">
                    <input type="hidden" id="reviewId" name="review_id">
                    <div class="mb-3">
                        <label>Nội dung phản hồi:</label>
                        <textarea class="form-control" id="replyText" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="submitReply">Gửi phản hồi</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
    let currentReviewId = null;

    // Xử lý nút phản hồi
    document.querySelectorAll('.reply-button, .edit-reply').forEach(button => {
        button.addEventListener('click', function() {
            currentReviewId = this.dataset.reviewId;
            const replyText = this.dataset.reply || '';
            document.getElementById('reviewId').value = currentReviewId;
            document.getElementById('replyText').value = replyText;
            replyModal.show();
        });
    });

    // Xử lý gửi phản hồi
    document.getElementById('submitReply').addEventListener('click', function() {
        const reply = document.getElementById('replyText').value;
        if (!reply.trim()) {
            alert('Vui lòng nhập nội dung phản hồi');
            return;
        }

        // Gửi phản hồi qua AJAX
        fetch('/nha-troSV/ajax/landlord-actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'reply_review',
                review_id: currentReviewId,
                reply: reply
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                replyModal.hide();
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => alert('Có lỗi xảy ra'));
    });
});
</script>
