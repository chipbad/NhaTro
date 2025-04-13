<?php
$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin chi tiết phòng trọ
$stmt = $conn->prepare("
    SELECT r.*, u.username as landlord_name, u.phone as landlord_phone, 
           u.email as landlord_email, COALESCE(u.avatar, 'assets/images/default-avatar.jpg') as landlord_avatar,
           u.created_at as landlord_joined,
           a.has_ac, a.has_parking, a.has_security, a.has_washing_machine,
           a.has_wifi, a.has_fridge, a.has_kitchen, a.has_private_wc,
           a.has_window, a.has_balcony, a.has_bed, a.has_wardrobe, a.has_tv
    FROM rooms r 
    LEFT JOIN users u ON r.landlord_id = u.id 
    LEFT JOIN amenities a ON r.id = a.room_id 
    WHERE r.id = ? AND r.status = 'available'
");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    redirect('index.php');
}

// Lấy đánh giá của phòng
$stmt = $conn->prepare("
    SELECT r.*, u.username as reviewer_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.room_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$room_id]);
$reviews = $stmt->fetchAll();

// Lấy ảnh của phòng
$stmt = $conn->prepare("SELECT * FROM room_images WHERE room_id = ? ORDER BY is_main DESC");
$stmt->execute([$room_id]);
$images = $stmt->fetchAll();

// Xử lý đánh giá mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $comment = cleanInput($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (room_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$room_id, $_SESSION['user_id'], $rating, $comment]);
        redirect("?page=room&id=$room_id");
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><?php echo htmlspecialchars($room['title']); ?></h2>
                    <div class="room-images mb-3">
                        <?php if (!empty($images)): ?>
                            <div id="roomCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php foreach ($images as $index => $image): ?>
                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                 class="d-block w-100 rounded" 
                                                 alt="Room image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($images) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#roomCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#roomCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <img src="assets/images/room-placeholder.jpg" class="img-fluid rounded" alt="Room image">
                        <?php endif; ?>
                    </div>
                    <div class="room-details">
                        <h4 class="text-primary mb-3"><?php echo number_format($room['price']); ?> VNĐ/tháng</h4>
                        <p class="room-description"><?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
                        
                        <h5>Thông tin chi tiết:</h5>
                        <ul class="list-unstyled">
                            <li><strong>Diện tích:</strong> <?php echo $room['area']; ?> m²</li>
                            <li><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($room['address']); ?></li>
                            <li><strong>Khu vực:</strong> <?php echo htmlspecialchars($room['district']); ?></li>
                        </ul>

                        <h5>Tiện ích:</h5>
                        <div class="amenities-grid">
                            <?php if ($room['has_ac']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-snowflake"></i> Điều hòa
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_parking']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-motorcycle"></i> Chỗ để xe
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_security']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-shield-alt"></i> Bảo vệ
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_wifi']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-wifi"></i> WiFi
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_fridge']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-cube"></i> Tủ lạnh
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_kitchen']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-utensils"></i> Nhà bếp
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_private_wc']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-toilet"></i> WC riêng
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_window']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-window-maximize"></i> Cửa sổ
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_balcony']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-door-open"></i> Ban công
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_bed']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-bed"></i> Giường
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_wardrobe']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-door-closed"></i> Tủ quần áo
                                </div>
                            <?php endif; ?>
                            <?php if ($room['has_tv']): ?>
                                <div class="amenity-item">
                                    <i class="fas fa-tv"></i> TV
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phần đánh giá -->
            <div class="card">
                <div class="card-body">
                    <h4>Đánh giá từ người thuê</h4>
                    
                    <?php if (isLoggedIn() && $_SESSION['role'] == 'student'): ?>
                        <form method="POST" class="mb-4">
                            <div class="mb-3">
                                <label>Đánh giá của bạn:</label>
                                <select name="rating" class="form-control" required>
                                    <option value="">Chọn số sao</option>
                                    <option value="5">⭐⭐⭐⭐⭐ (Rất tốt)</option>
                                    <option value="4">⭐⭐⭐⭐ (Tốt)</option>
                                    <option value="3">⭐⭐⭐ (Bình thường)</option>
                                    <option value="2">⭐⭐ (Tệ)</option>
                                    <option value="1">⭐ (Rất tệ)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nhận xét:</label>
                                <textarea name="comment" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item border-bottom py-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                        <div class="text-warning">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $review['rating'] ? '⭐' : '☆';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mt-2"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                
                                <?php if (!empty($review['reply'])): ?>
                                    <div class="mt-3 ms-4 p-3 bg-light rounded">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-reply text-muted me-2"></i>
                                            <strong>Phản hồi từ chủ trọ:</strong>
                                        </div>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['reply'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Chưa có đánh giá nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Thông tin liên hệ -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px">
                <div class="card-body">
                    <h5 class="card-title mb-4">Thông tin liên hệ</h5>
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo !empty($room['landlord_avatar']) ? $room['landlord_avatar'] : 'assets/images/default-avatar.jpg'; ?>" 
                             class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($room['landlord_name']); ?></h6>
                            <small class="text-muted">
                                <i class="fas fa-user-clock"></i> 
                                Tham gia từ: <?php echo date('m/Y', strtotime($room['landlord_joined'])); ?>
                            </small>
                        </div>
                    </div>

                    <?php if (isLoggedIn()): ?>
                        <div class="contact-info mb-3">
                            <p class="mb-2">
                                <i class="fas fa-phone text-success"></i>
                                <strong>Điện thoại:</strong><br>
                                <a href="tel:<?php echo $room['landlord_phone']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($room['landlord_phone']); ?>
                                </a>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope text-primary"></i>
                                <strong>Email:</strong><br>
                                <a href="mailto:<?php echo $room['landlord_email']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($room['landlord_email']); ?>
                                </a>
                            </p>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="tel:<?php echo $room['landlord_phone']; ?>" class="btn btn-success">
                                <i class="fas fa-phone"></i> Gọi điện
                            </a>
                            <a href="index.php?page=chat&user=<?php echo $room['landlord_id']; ?>&room=<?php echo $room['id']; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-comment"></i> Nhắn tin
                            </a>
                            <?php if ($_SESSION['role'] == 'student'): ?>
                                <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#scheduleModal">
                                    <i class="fas fa-calendar-check"></i> Đặt lịch xem phòng
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Vui lòng <a href="?page=login" class="alert-link">đăng nhập</a> để xem thông tin liên hệ
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal đặt lịch -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đặt lịch xem phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                    <input type="hidden" name="landlord_id" value="<?php echo $room['landlord_id']; ?>">
                    
                    <div class="mb-3">
                        <label>Ngày xem:</label>
                        <input type="date" name="viewing_date" class="form-control" required
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>Giờ xem:</label>
                        <select name="viewing_time" class="form-control" required>
                            <?php
                            for ($hour = 8; $hour <= 20; $hour++) {
                                printf('<option value="%02d:00">%02d:00</option>', $hour, $hour);
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label>Ghi chú:</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="submitSchedule">Đặt lịch</button>
            </div>
        </div>
    </div>
</div>

<!-- Thay thế đoạn script cũ bằng script mới -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saveBtn = document.querySelector('.save-room-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            const action = this.dataset.action;
            
            fetch('/nha-troSV/ajax/student-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}_room&room_id=${roomId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Cập nhật UI
                    if (action === 'save') {
                        this.classList.replace('btn-outline-primary', 'btn-danger');
                        this.dataset.action = 'unsave';
                        this.innerHTML = '<i class="fas fa-heart-broken"></i> Bỏ lưu phòng';
                    } else {
                        this.classList.replace('btn-danger', 'btn-outline-primary');
                        this.dataset.action = 'save';
                        this.innerHTML = '<i class="fas fa-heart"></i> Lưu phòng';
                    }
                    alert(data.message);
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => alert('Có lỗi xảy ra'));
        });
    }
});

document.getElementById('submitSchedule').addEventListener('click', function() {
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);
    
    fetch('/nha-troSV/ajax/schedule-viewing.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Đặt lịch xem phòng thành công!');
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => alert('Có lỗi xảy ra'));
});
</script>
