<?php
if (!isLoggedIn() || !isLandlord()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = cleanInput($_POST['title']);
    $description = cleanInput($_POST['description']);
    $price = (float)$_POST['price'];
    $area = (float)$_POST['area'];
    $address = cleanInput($_POST['address']);
    $district = cleanInput($_POST['district']);
    
    $has_ac = isset($_POST['has_ac']) ? 1 : 0;
    $has_parking = isset($_POST['has_parking']) ? 1 : 0;
    $has_security = isset($_POST['has_security']) ? 1 : 0;
    $has_wifi = isset($_POST['has_wifi']) ? 1 : 0;
    $has_fridge = isset($_POST['has_fridge']) ? 1 : 0;
    $has_kitchen = isset($_POST['has_kitchen']) ? 1 : 0;
    $has_private_wc = isset($_POST['has_private_wc']) ? 1 : 0;
    $has_window = isset($_POST['has_window']) ? 1 : 0;
    $has_balcony = isset($_POST['has_balcony']) ? 1 : 0;
    $has_bed = isset($_POST['has_bed']) ? 1 : 0;
    $has_wardrobe = isset($_POST['has_wardrobe']) ? 1 : 0;
    $has_tv = isset($_POST['has_tv']) ? 1 : 0;
    
    // Kiểm tra và tạo thư mục upload nếu chưa tồn tại
    $upload_dir = 'uploads/rooms/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    try {
        $conn->beginTransaction();
        
        // Thêm thông tin phòng
        $sql = "INSERT INTO rooms (landlord_id, title, description, price, area, address, district) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $area, $address, $district]);
        
        $room_id = $conn->lastInsertId();
        
        // Thêm tiện ích
        $sql = "INSERT INTO amenities (room_id, has_ac, has_parking, has_security, 
                has_wifi, has_fridge, has_kitchen, has_private_wc, has_window, 
                has_balcony, has_bed, has_wardrobe, has_tv) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$room_id, $has_ac, $has_parking, $has_security, 
                       $has_wifi, $has_fridge, $has_kitchen, $has_private_wc, 
                       $has_window, $has_balcony, $has_bed, $has_wardrobe, $has_tv]);

        // Xử lý upload ảnh
        if (!empty($_FILES['room_images']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            foreach ($_FILES['room_images']['tmp_name'] as $key => $tmp_name) {
                $file_type = $_FILES['room_images']['type'][$key];
                $file_size = $_FILES['room_images']['size'][$key];
                $file_error = $_FILES['room_images']['error'][$key];

                if ($file_error === UPLOAD_ERR_OK && 
                    in_array($file_type, $allowed_types) && 
                    $file_size <= $max_size) {
                    
                    $file_name = time() . '_' . basename($_FILES['room_images']['name'][$key]);
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Đánh dấu ảnh đầu tiên là ảnh chính
                        $is_main = ($key === 0) ? 1 : 0;
                        
                        $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path, is_main) VALUES (?, ?, ?)");
                        $stmt->execute([$room_id, $file_path, $is_main]);
                    }
                }
            }
        }
        
        $conn->commit();
        redirect('?page=landlord-dashboard&action=rooms');

    } catch(Exception $e) {
        $conn->rollBack();
        $error = "Có lỗi xảy ra khi thêm phòng trọ: " . $e->getMessage();
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Thêm phòng trọ mới</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Tiêu đề:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Mô tả:</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Giá thuê (VNĐ/tháng):</label>
                                    <input type="number" name="price" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Diện tích (m²):</label>
                                    <input type="number" name="area" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Địa chỉ:</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Quận/Huyện:</label>
                            <select name="district" class="form-control" required>
                                <option value="">Chọn quận/huyện</option>
                                <?php foreach (getHanoiDistricts() as $value => $label): ?>
                                    <option value="<?php echo $value; ?>">
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label>Tiện ích:</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="has_ac" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-snowflake"></i> Điều hòa</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_parking" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-motorcycle"></i> Chỗ để xe</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_security" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-shield-alt"></i> Bảo vệ</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="has_wifi" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-wifi"></i> WiFi</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_fridge" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-cube"></i> Tủ lạnh</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_kitchen" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-utensils"></i> Nhà bếp</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_private_wc" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-toilet"></i> WC riêng</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="has_window" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-window-maximize"></i> Cửa sổ</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_balcony" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-door-open"></i> Ban công</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_bed" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-bed"></i> Giường</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_wardrobe" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-door-closed"></i> Tủ quần áo</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="has_tv" class="form-check-input">
                                        <label class="form-check-label"><i class="fas fa-tv"></i> TV</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Hình ảnh phòng trọ:</label>
                            <input type="file" name="room_images[]" class="form-control" multiple 
                                   accept=".jpg,.jpeg,.png" required>
                            <small class="text-muted">
                                Có thể chọn nhiều ảnh. Chỉ chấp nhận file JPG, JPEG, PNG. Tối đa 5MB mỗi ảnh.
                                Ảnh đầu tiên sẽ được sử dụng làm ảnh đại diện.
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Thêm phòng trọ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
