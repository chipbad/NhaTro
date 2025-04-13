<?php
// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_id = (int)$_POST['room_id'];
    $action = $_POST['action'];
    
    switch($action) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ?");
            $stmt->execute([$room_id]);
            break;
            
        case 'reject':
            $stmt = $conn->prepare("UPDATE rooms SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$room_id]);
            break;
            
        case 'delete':
            // Xóa amenities trước vì có khóa ngoại
            $stmt = $conn->prepare("DELETE FROM amenities WHERE room_id = ?");
            $stmt->execute([$room_id]);
            
            // Xóa reviews
            $stmt = $conn->prepare("DELETE FROM reviews WHERE room_id = ?");
            $stmt->execute([$room_id]);
            
            // Sau đó xóa phòng
            $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->execute([$room_id]);
            break;
    }
}
?>

<?php
// Thêm chi tiết truy vấn SQL để lấy thêm thông tin
$sql = "SELECT r.*, u.username as landlord_name, u.phone as landlord_phone,
        a.has_ac, a.has_parking, a.has_security, a.has_washing_machine,
        a.has_wifi, a.has_fridge, a.has_kitchen, a.has_private_wc,
        a.has_window, a.has_balcony, a.has_bed, a.has_wardrobe, a.has_tv,
        (SELECT image_path FROM room_images WHERE room_id = r.id AND is_main = 1 LIMIT 1) as main_image
        FROM rooms r 
        JOIN users u ON r.landlord_id = u.id 
        LEFT JOIN amenities a ON r.id = a.room_id 
        ORDER BY FIELD(r.status, 'pending', 'available', 'rented', 'rejected'), r.created_at DESC";
$stmt = $conn->query($sql);

?>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title">Quản lý phòng trọ</h5>
                <div class="d-flex gap-2">
                    <select class="form-select" id="statusFilter" style="width: 150px">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="available">Đã duyệt</option>
                        <option value="rejected">Đã từ chối</option>
                    </select>
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Tìm kiếm..." style="width: 200px">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Chủ trọ</th>
                            <th>Giá (VNĐ)</th>
                            <th>Trạng thái</th>
                            <th>Ngày đăng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($room = $stmt->fetch()): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $room['main_image'] ?? 'assets/images/room-placeholder.jpg'; ?>" 
                                         class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                </td>
                                <td>
                                    <a href="#" class="view-room" 
                                       data-room='<?php echo json_encode($room); ?>'>
                                        <?php echo htmlspecialchars($room['title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($room['landlord_name']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo $room['landlord_phone']; ?></small>
                                </td>
                                <td><?php echo number_format($room['price']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeColor($room['status']); ?>">
                                        <?php echo getStatusText($room['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($room['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?page=admin&action=edit-room&id=<?php echo $room['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <?php if ($room['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-success approve-room" 
                                                    data-room-id="<?php echo $room['id']; ?>">
                                                <i class="fas fa-check"></i> Duyệt
                                            </button>
                                            <button class="btn btn-sm btn-warning reject-room" 
                                                    data-room-id="<?php echo $room['id']; ?>">
                                                <i class="fas fa-times"></i> Từ chối
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-sm btn-danger delete-room" 
                                            data-room-id="<?php echo $room['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết phòng -->
<div class="modal fade" id="roomDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết phòng trọ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="" id="modalRoomImage" class="img-fluid rounded">
                    </div>
                    <div class="col-md-6">
                        <h5 id="modalRoomTitle"></h5>
                        <p class="text-primary" id="modalRoomPrice"></p>
                        <p id="modalRoomDescription"></p>
                        
                        <h6>Thông tin cơ bản:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Diện tích:</strong> <span id="modalRoomArea"></span></li>
                            <li><strong>Địa chỉ:</strong> <span id="modalRoomAddress"></span></li>
                            <li><strong>Khu vực:</strong> <span id="modalRoomDistrict"></span></li>
                        </ul>

                        <h6>Tiện ích:</h6>
                        <div id="modalRoomAmenities" class="d-flex flex-wrap gap-2">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="modalActions">
                <!-- Buttons will be added dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomModal = new bootstrap.Modal(document.getElementById('roomDetailModal'));
    const amenityIcons = {
        has_ac: '<i class="fas fa-snowflake"></i> Điều hòa',
        has_parking: '<i class="fas fa-motorcycle"></i> Chỗ để xe',
        has_security: '<i class="fas fa-shield-alt"></i> Bảo vệ',
        has_wifi: '<i class="fas fa-wifi"></i> WiFi',
        has_fridge: '<i class="fas fa-cube"></i> Tủ lạnh',
        has_kitchen: '<i class="fas fa-utensils"></i> Nhà bếp',
        has_private_wc: '<i class="fas fa-toilet"></i> WC riêng',
        has_window: '<i class="fas fa-window-maximize"></i> Cửa sổ',
        has_balcony: '<i class="fas fa-door-open"></i> Ban công',
        has_bed: '<i class="fas fa-bed"></i> Giường',
        has_wardrobe: '<i class="fas fa-door-closed"></i> Tủ quần áo',
        has_tv: '<i class="fas fa-tv"></i> TV'
    };

    // Xem chi tiết phòng
    document.querySelectorAll('.view-room').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const room = JSON.parse(this.dataset.room);
            
            document.getElementById('modalRoomImage').src = room.main_image || 'assets/images/room-placeholder.jpg';
            document.getElementById('modalRoomTitle').textContent = room.title;
            document.getElementById('modalRoomPrice').textContent = numberFormat(room.price) + ' VNĐ/tháng';
            document.getElementById('modalRoomDescription').textContent = room.description;
            document.getElementById('modalRoomArea').textContent = room.area + ' m²';
            document.getElementById('modalRoomAddress').textContent = room.address;
            document.getElementById('modalRoomDistrict').textContent = room.district;

            // Hiển thị tiện ích
            const amenitiesContainer = document.getElementById('modalRoomAmenities');
            amenitiesContainer.innerHTML = '';
            Object.entries(amenityIcons).forEach(([key, value]) => {
                if (room[key] == 1) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-light text-dark';
                    badge.innerHTML = value;
                    amenitiesContainer.appendChild(badge);
                }
            });

            // Thêm nút thao tác
            const actionsContainer = document.getElementById('modalActions');
            actionsContainer.innerHTML = '';
            if (room.status === 'pending') {
                actionsContainer.innerHTML = `
                    <button class="btn btn-success approve-room" data-room-id="${room.id}">
                        <i class="fas fa-check"></i> Duyệt
                    </button>
                    <button class="btn btn-warning reject-room" data-room-id="${room.id}">
                        <i class="fas fa-times"></i> Từ chối
                    </button>
                `;
            }

            roomModal.show();
        });
    });

    // Xử lý lọc và tìm kiếm
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');

    function filterTable() {
        const status = statusFilter.value.toLowerCase();
        const search = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const rowStatus = row.querySelector('.badge').textContent.toLowerCase();
            const rowText = row.textContent.toLowerCase();
            const statusMatch = !status || rowStatus.includes(status);
            const searchMatch = !search || rowText.includes(search);
            row.style.display = statusMatch && searchMatch ? '' : 'none';
        });
    }

    statusFilter.addEventListener('change', filterTable);
    searchInput.addEventListener('input', filterTable);

    // Helper function
    function numberFormat(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    // Xử lý duyệt phòng
    document.querySelectorAll('.approve-room').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn duyệt phòng này?')) {
                const roomId = this.dataset.roomId;
                processRoom('approve_room', roomId);
            }
        });
    });

    // Xử lý từ chối phòng
    document.querySelectorAll('.reject-room').forEach(button => {
        button.addEventListener('click', function() {
            const roomId = this.dataset.roomId;
            const reason = prompt('Nhập lý do từ chối:');
            if (reason !== null) {
                processRoom('reject_room', roomId, reason);
            }
        });
    });

    function processRoom(action, roomId, reason = '') {
        fetch('/nha-troSV/ajax/admin-actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}&room_id=${roomId}&reason=${encodeURIComponent(reason)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => alert('Có lỗi xảy ra'));
    }
});
</script>
