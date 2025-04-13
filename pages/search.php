<?php
// Get filter parameters
$location = isset($_GET['location']) ? cleanInput($_GET['location']) : '';
$price_range = isset($_GET['price']) ? (int)$_GET['price'] : 0;
$district = isset($_GET['district']) ? cleanInput($_GET['district']) : '';
$area = isset($_GET['area']) ? cleanInput($_GET['area']) : '';
$amenities = isset($_GET['amenities']) ? $_GET['amenities'] : [];

// Build base query
$sql = "SELECT r.*, u.username as landlord_name, 
        a.has_ac, a.has_parking, a.has_security, a.has_washing_machine 
        FROM rooms r 
        LEFT JOIN users u ON r.landlord_id = u.id 
        LEFT JOIN amenities a ON r.id = a.room_id 
        WHERE r.status = 'available'";
$params = [];

// Add filters
if (!empty($location)) {
    $sql .= " AND (r.address LIKE ? OR r.district LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}

if (!empty($district)) {
    $sql .= " AND r.district = ?";
    $params[] = $district;
}

// Price range filter
switch($price_range) {
    case 1: // Dưới 2 triệu
        $sql .= " AND r.price < 2000000";
        break;
    case 2: // 2-3 triệu
        $sql .= " AND r.price BETWEEN 2000000 AND 3000000";
        break;
    case 3: // 3-5 triệu
        $sql .= " AND r.price BETWEEN 3000000 AND 5000000";
        break;
}

// Execute query
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>

<div class="container">
    <div class="row">
        <!-- Filter sidebar -->
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Bộ lọc tìm kiếm</h5>
                    <form action="" method="GET">
                        <input type="hidden" name="page" value="search">
                        
                        <div class="mb-3">
                            <label>Khu vực:</label>
                            <input type="text" name="location" class="form-control" 
                                   value="<?php echo htmlspecialchars($location); ?>">
                        </div>

                        <div class="mb-3">
                            <label>Quận/Huyện:</label>
                            <select name="district" class="form-control">
                                <option value="">Tất cả khu vực</option>
                                <?php foreach (getHanoiDistricts() as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $district == $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Khoảng giá:</label>
                            <select name="price" class="form-control">
                                <option value="">Tất cả</option>
                                <option value="1" <?php echo $price_range == 1 ? 'selected' : ''; ?>>Dưới 2 triệu</option>
                                <option value="2" <?php echo $price_range == 2 ? 'selected' : ''; ?>>2-3 triệu</option>
                                <option value="3" <?php echo $price_range == 3 ? 'selected' : ''; ?>>3-5 triệu</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Tiện ích:</label>
                            <div class="form-check">
                                <input type="checkbox" name="amenities[]" value="ac" class="form-check-input">
                                <label class="form-check-label">Điều hòa</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="amenities[]" value="parking" class="form-check-input">
                                <label class="form-check-label">Chỗ để xe</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="amenities[]" value="security" class="form-check-input">
                                <label class="form-check-label">Bảo vệ 24/7</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search results -->
        <div class="col-md-9">
            <h4 class="mb-3">Kết quả tìm kiếm (<?php echo count($rooms); ?> phòng)</h4>
            
            <div class="row">
                <?php foreach ($rooms as $room): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <img src="assets/images/room-placeholder.jpg" class="card-img-top" alt="...">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($room['title']); ?></h5>
                                <p class="card-text">
                                    <strong>Giá:</strong> <?php echo number_format($room['price']); ?> VNĐ/tháng<br>
                                    <strong>Diện tích:</strong> <?php echo $room['area']; ?>m²<br>
                                    <strong>Khu vực:</strong> <?php echo htmlspecialchars($room['district']); ?><br>
                                    <strong>Tiện ích:</strong>
                                    <?php
                                    $amenities = [];
                                    if ($room['has_ac']) $amenities[] = 'Điều hòa';
                                    if ($room['has_parking']) $amenities[] = 'Chỗ để xe';
                                    if ($room['has_security']) $amenities[] = 'Bảo vệ 24/7';
                                    echo implode(', ', $amenities);
                                    ?>
                                </p>
                                <a href="?page=room&id=<?php echo $room['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
