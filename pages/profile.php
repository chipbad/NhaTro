<?php
if (!isLoggedIn()) {
    redirect('?page=login');
}

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $phone = cleanInput($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $errors = [];

    // Kiểm tra mật khẩu hiện tại nếu muốn đổi mật khẩu
    if (!empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Mật khẩu hiện tại không chính xác";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            $sql = "UPDATE users SET username = ?, phone = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username, $phone, password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        } else {
            $sql = "UPDATE users SET username = ?, phone = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username, $phone, $_SESSION['user_id']]);
        }
        $_SESSION['username'] = $username;
        $success = "Cập nhật thông tin thành công!";
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thông tin cá nhân</h5>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Tên người dùng:</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Email:</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label>Số điện thoại:</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <hr>
                        <h6>Đổi mật khẩu (để trống nếu không đổi)</h6>
                        
                        <div class="mb-3">
                            <label>Mật khẩu hiện tại:</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label>Mật khẩu mới:</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if (isLandlord()): ?>
                <!-- Phần dành cho chủ trọ -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Danh sách phòng trọ của tôi</h5>
                        <a href="?page=room-add" class="btn btn-success mb-3">Thêm phòng mới</a>
                        
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM rooms WHERE landlord_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$_SESSION['user_id']]);
                        $rooms = $stmt->fetchAll();
                        
                        if ($rooms): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Tiêu đề</th>
                                            <th>Giá</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rooms as $room): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($room['title']); ?></td>
                                                <td><?php echo number_format($room['price']); ?> VNĐ</td>
                                                <td><?php echo $room['status']; ?></td>
                                                <td>
                                                    <a href="?page=room-edit&id=<?php echo $room['id']; ?>" 
                                                       class="btn btn-sm btn-primary">Sửa</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>Bạn chưa có phòng trọ nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Phần dành cho sinh viên -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Phòng trọ đã quan tâm</h5>
                        <!-- Thêm code hiển thị danh sách phòng đã lưu hoặc đã liên hệ -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
