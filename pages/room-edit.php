<?php
if (!isLoggedIn() || !isLandlord()) {
    redirect('index.php');
}

$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Cập nhật để lấy tất cả thông tin phòng trọ và hình ảnh
$stmt = $conn->prepare("
    SELECT r.*, a.*, GROUP_CONCAT(ri.id) as image_ids, GROUP_CONCAT(ri.image_path) as image_paths
    FROM rooms r 
    LEFT JOIN amenities a ON r.id = a.room_id
    LEFT JOIN room_images ri ON r.id = ri.room_id
    WHERE r.id = ? AND r.landlord_id = ?
    GROUP BY r.id
");
$stmt->execute([$room_id, $_SESSION['user_id']]);
$room = $stmt->fetch();

if (!$room) {
    redirect('?page=profile');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Cập nhật thông tin cơ bản
        $sql = "UPDATE rooms SET 
                title = ?, description = ?, price = ?, area = ?, 
                address = ?, district = ?, status = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            cleanInput($_POST['title']),
            cleanInput($_POST['description']),
            (float)$_POST['price'],
            (float)$_POST['area'],
            cleanInput($_POST['address']),
            cleanInput($_POST['district']),
            cleanInput($_POST['status']),
            $room_id
        ]);
        
        // Cập nhật tiện ích
        $sql = "UPDATE amenities SET 
                has_ac = ?, has_parking = ?, has_security = ?,
                has_wifi = ?, has_fridge = ?, has_kitchen = ?,
                has_private_wc = ?, has_window = ?, has_balcony = ?,
                has_bed = ?, has_wardrobe = ?, has_tv = ?
                WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            isset($_POST['has_ac']) ? 1 : 0,
            isset($_POST['has_parking']) ? 1 : 0,
            isset($_POST['has_security']) ? 1 : 0,
            isset($_POST['has_wifi']) ? 1 : 0,
            isset($_POST['has_fridge']) ? 1 : 0,
            isset($_POST['has_kitchen']) ? 1 : 0,
            isset($_POST['has_private_wc']) ? 1 : 0,
            isset($_POST['has_window']) ? 1 : 0,
            isset($_POST['has_balcony']) ? 1 : 0,
            isset($_POST['has_bed']) ? 1 : 0,
            isset($_POST['has_wardrobe']) ? 1 : 0,
            isset($_POST['has_tv']) ? 1 : 0,
            $room_id
        ]);

        // Xử lý xóa ảnh
        if (!empty($_POST['delete_images'])) {
            $delete_ids = explode(',', $_POST['delete_images']);
            foreach ($delete_ids as $image_id) {
                $stmt = $conn->prepare("SELECT image_path FROM room_images WHERE id = ? AND room_id = ?");
                $stmt->execute([(int)$image_id, $room_id]);
                $image = $stmt->fetch();
                
                if ($image && file_exists($image['image_path'])) {
                    unlink($image['image_path']);
                }
                
                $stmt = $conn->prepare("DELETE FROM room_images WHERE id = ? AND room_id = ?");
                $stmt->execute([(int)$image_id, $room_id]);
            }
        }

        // Xử lý thêm ảnh mới
        if (!empty($_FILES['new_images']['name'][0])) {
            $upload_dir = 'uploads/rooms/';
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                $file_type = $_FILES['new_images']['type'][$key];
                $file_size = $_FILES['new_images']['size'][$key];
                
                if ($file_size <= $max_size && in_array($file_type, $allowed_types)) {
                    $file_name = time() . '_' . basename($_FILES['new_images']['name'][$key]);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
                        $stmt->execute([$room_id, $file_path]);
                    }
                }
            }
        }
        
        $conn->commit();
        $success = "Cập nhật phòng trọ thành công!";
        
    } catch(Exception $e) {
        $conn->rollBack();
        $error = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

// Lấy danh sách hình ảnh hiện tại
$stmt = $conn->prepare("SELECT * FROM room_images WHERE room_id = ?");
$stmt->execute([$room_id]);
$current_images = $stmt->fetchAll();
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Sửa thông tin phòng trọ</h3>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="editRoomForm">
                        <!-- Thông tin cơ bản -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <h5>Thông tin cơ bản</h5>
                                <div class="mb-3">
                                    <label>Tiêu đề:</label>
                                    <input type="text" name="title" class="form-control" required
                                           value="<?php echo htmlspecialchars($room['title']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label>Mô tả:</label>
                                    <textarea name="description" class="form-control" rows="4" required><?php 
                                        echo htmlspecialchars($room['description']); 
                                    ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label>Giá thuê (VNĐ/tháng):</label>
                                            <input type="number" name="price" class="form-control" required
                                                   value="<?php echo $room['price']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label>Diện tích (m²):</label>
                                            <input type="number" name="area" class="form-control" required
                                                   value="<?php echo $room['area']; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label>Địa chỉ:</label>
                                    <input type="text" name="address" class="form-control" required
                                           value="<?php echo htmlspecialchars($room['address']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label>Quận/Huyện:</label>
                                    <select name="district" class="form-control" required>
                                        <?php foreach (getHanoiDistricts() as $value => $label): ?>
                                            <option value="<?php echo $value; ?>" <?php echo $room['district'] == $value ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h5>Trạng thái</h5>
                                <select name="status" class="form-control">
                                    <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Còn trống</option>
                                    <option value="rented" <?php echo $room['status'] == 'rented' ? 'selected' : ''; ?>>Đã cho thuê</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quản lý hình ảnh -->
                        <div class="mb-4">
                            <h5>Hình ảnh phòng trọ</h5>
                            <div class="row" id="imageGallery">
                                <?php foreach ($current_images as $image): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="position-relative">
                                            <img src="<?php echo $image['image_path']; ?>" class="img-thumbnail" alt="Room image">
                                            <div class="form-check position-absolute top-0 end-0 m-2">
                                                <input type="checkbox" class="form-check-input delete-image" 
                                                       value="<?php echo $image['id']; ?>" 
                                                       data-image-id="<?php echo $image['id']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="delete_images" id="deleteImages">
                            
                            <div class="mt-3">
                                <label>Thêm ảnh mới:</label>
                                <input type="file" name="new_images[]" class="form-control" multiple accept=".jpg,.jpeg,.png">
                                <small class="text-muted">Có thể chọn nhiều ảnh. Tối đa 5MB mỗi ảnh.</small>
                            </div>
                        </div>

                        <!-- Tiện ích -->
                        <div class="mb-4">
                            <h5>Tiện ích</h5>
                            <div class="row">
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_ac" class="form-check-input"
                                           <?php echo $room['has_ac'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Điều hòa</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_parking" class="form-check-input"
                                           <?php echo $room['has_parking'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Chỗ để xe</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_security" class="form-check-input"
                                           <?php echo $room['has_security'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Bảo vệ 24/7</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_wifi" class="form-check-input"
                                           <?php echo $room['has_wifi'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Wi-Fi</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_fridge" class="form-check-input"
                                           <?php echo $room['has_fridge'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Tủ lạnh</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_kitchen" class="form-check-input"
                                           <?php echo $room['has_kitchen'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Bếp</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_private_wc" class="form-check-input"
                                           <?php echo $room['has_private_wc'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">WC riêng</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_window" class="form-check-input"
                                           <?php echo $room['has_window'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Cửa sổ</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_balcony" class="form-check-input"
                                           <?php echo $room['has_balcony'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Ban công</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_bed" class="form-check-input"
                                           <?php echo $room['has_bed'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Giường</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_wardrobe" class="form-check-input"
                                           <?php echo $room['has_wardrobe'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Tủ quần áo</label>
                                </div>
                                <div class="form-check col-md-4">
                                    <input type="checkbox" name="has_tv" class="form-check-input"
                                           <?php echo $room['has_tv'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">TV</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                            <a href="?page=landlord-dashboard&action=rooms" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editRoomForm').addEventListener('submit', function(e) {
    const deleteImages = [];
    document.querySelectorAll('.delete-image:checked').forEach(checkbox => {
        deleteImages.push(checkbox.value);
    });
    document.getElementById('deleteImages').value = deleteImages.join(',');
});
</script>
