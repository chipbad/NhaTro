function handleAjaxResponse(response) {
    if (response.status === 'success') {
        alert(response.message);
        location.reload();
    } else {
        alert(response.message || 'Có lỗi xảy ra');
    }
}

function sendAjaxRequest(action, roomId, additionalData = {}) {
    const data = {
        action: action,
        room_id: roomId,
        ...additionalData
    };

    fetch('/nha-troSV/ajax/landlord-actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .then(handleAjaxResponse)
    .catch(error => alert('Có lỗi xảy ra'));
}

document.addEventListener('DOMContentLoaded', function() {
    // Xử lý đánh dấu đã cho thuê
    document.querySelectorAll('.mark-rented').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn đánh dấu phòng này đã cho thuê?')) {
                sendAjaxRequest('mark_rented', this.dataset.roomId);
            }
        });
    });

    // Xử lý đánh dấu còn trống
    document.querySelectorAll('.mark-available').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn đánh dấu phòng này còn trống?')) {
                sendAjaxRequest('mark_available', this.dataset.roomId);
            }
        });
    });

    // Xử lý xóa phòng
    document.querySelectorAll('.delete-room').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa phòng này? Hành động này không thể hoàn tác!')) {
                sendAjaxRequest('delete_room', this.dataset.roomId);
            }
        });
    });

    // Xử lý phản hồi đánh giá
    document.querySelectorAll('.reply-button').forEach(button => {
        button.addEventListener('click', function() {
            const reply = prompt('Nhập phản hồi của bạn:');
            if (reply) {
                sendAjaxRequest('reply_review', null, {
                    review_id: this.dataset.reviewId,
                    reply: reply
                });
            }
        });
    });
});
