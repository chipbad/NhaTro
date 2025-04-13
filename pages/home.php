<div class="hero-section bg-light py-5 text-center">
    <h1>Tìm phòng trọ sinh viên</h1>
    <p class="lead">Kết nối bạn với hàng ngàn phòng trọ chất lượng</p>
    
    <div class="container">
        <form action="?page=search" method="GET" class="row g-3 justify-content-center">
            <div class="col-md-4">
                <input type="text" name="location" class="form-control" placeholder="Nhập khu vực...">
            </div>
            <div class="col-md-2">
                <select name="price" class="form-control">
                    <option value="">Giá</option>
                    <option value="1">Dưới 2 triệu</option>
                    <option value="2">2-3 triệu</option>
                    <option value="3">3-5 triệu</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
            </div>
        </form>
    </div>
</div>

<div class="container mt-5">
    <h2>Phòng trọ nổi bật</h2>
    <div class="row">
        <?php
        $sql = "SELECT r.*, u.username as landlord_name 
                FROM rooms r 
                JOIN users u ON r.landlord_id = u.id 
                WHERE r.status = 'available' 
                ORDER BY r.created_at DESC 
                LIMIT 6";
        $stmt = $conn->query($sql);
        while ($room = $stmt->fetch()) {
            ?>
            <div class="col-md-4 mb-4">
                <div class="room-card">
                    <div class="card-img-wrapper">
                        <img src="assets/images/placeholders/room.jpg" class="card-img-top" alt="Room image">
                        <div class="price-tag">
                            <i class="fas fa-tag"></i> <?php echo number_format($room['price']); ?> VNĐ
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($room['title']); ?></h5>
                        <div class="amenities-grid">
                            <div class="amenity-item">
                                <i class="fas fa-ruler-combined"></i>
                                <?php echo $room['area']; ?>m²
                            </div>
                            <div class="amenity-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($room['district']); ?>
                            </div>
                        </div>
                        <a href="?page=room&id=<?php echo $room['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-info-circle"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
