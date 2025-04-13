<?php
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin phòng
$stmt = $conn->prepare("
    SELECT r.*, a.*, u.username as landlord_name 
    FROM rooms r 
    LEFT JOIN amenities a ON r.id = a.room_id
    LEFT JOIN users u ON r.landlord_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    redirect('?page=admin&action=rooms');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Cập nhật thông tin phòng
        $stmt = $conn->prepare("
            UPDATE rooms SET 
                title = ?, description = ?, price = ?, area = ?,
                address = ?, district = ?, status = ?
            WHERE id = ?
        ");
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
        $stmt = $conn->prepare("
            UPDATE amenities SET 
                has_ac = ?, has_parking = ?, has_security = ?,
                has_wifi = ?, has_fridge = ?, has_kitchen = ?,
                has_private_wc = ?, has_window = ?, has_balcony = ?,
                has_bed = ?, has_wardrobe = ?, has_tv = ?
            WHERE room_id = ?
        ");
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

        $conn->commit();
        $success = "Cập nhật thành công!";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Có lỗi xảy ra: " . $e->getMessage();
    }
}
?>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title">Chỉnh sửa phòng trọ</h5>
                <a href="?page=admin&action=rooms" class="btn btn-secondary">Quay lại</a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
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
                            <label>Khu vực:</label>
                            <select name="district" class="form-control" required>
                                <?php foreach (getHanoiDistricts() as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" 
                                            <?php echo $room['district'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Trạng thái:</label>
                            <select name="status" class="form-control" required>
                                <option value="pending" <?php echo $room['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Chờ duyệt
                                </option>
                                <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>
                                    Đã duyệt
                                </option>
                                <option value="rented" <?php echo $room['status'] == 'rented' ? 'selected' : ''; ?>>
                                    Đã cho thuê
                                </option>
                                <option value="rejected" <?php echo $room['status'] == 'rejected' ? 'selected' : ''; ?>>
                                    Từ chối
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h6>Thông tin chủ trọ</h6>
                                <p class="mb-0">
                                    <strong>Chủ trọ:</strong> <?php echo htmlspecialchars($room['landlord_name']); ?>
                                </p>
                            </div>
                        </div>

                        <div class="card mt-3">
                            <div class="card-body">
                                <h6>Tiện ích</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_ac" class="form-check-input"
                                                   <?php echo $room['has_ac'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-snowflake"></i> Điều hòa
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_parking" class="form-check-input"
                                                   <?php echo $room['has_parking'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-motorcycle"></i> Chỗ để xe
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_security" class="form-check-input"
                                                   <?php echo $room['has_security'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-shield-alt"></i> Bảo vệ
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_wifi" class="form-check-input"
                                                   <?php echo $room['has_wifi'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-wifi"></i> WiFi
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_fridge" class="form-check-input"
                                                   <?php echo $room['has_fridge'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-cube"></i> Tủ lạnh
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_kitchen" class="form-check-input"
                                                   <?php echo $room['has_kitchen'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-utensils"></i> Nhà bếp
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_private_wc" class="form-check-input"
                                                   <?php echo $room['has_private_wc'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-toilet"></i> WC riêng
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_window" class="form-check-input"
                                                   <?php echo $room['has_window'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-window-maximize"></i> Cửa sổ
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_balcony" class="form-check-input"
                                                   <?php echo $room['has_balcony'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-door-open"></i> Ban công
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_bed" class="form-check-input"
                                                   <?php echo $room['has_bed'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-bed"></i> Giường
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_wardrobe" class="form-check-input"
                                                   <?php echo $room['has_wardrobe'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-door-closed"></i> Tủ quần áo
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="has_tv" class="form-check-input"
                                                   <?php echo $room['has_tv'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label">
                                                <i class="fas fa-tv"></i> TV
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
