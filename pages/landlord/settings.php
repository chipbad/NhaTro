<?php
// Lấy thông tin chủ trọ
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$landlord = $stmt->fetch();

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $errors = [];

    switch ($action) {
        case 'update_profile':
            $username = cleanInput($_POST['username']);
            $phone = cleanInput($_POST['phone']);
            
            if (empty($errors)) {
                $stmt = $conn->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
                $stmt->execute([$username, $phone, $_SESSION['user_id']]);
                $_SESSION['username'] = $username;
                $success = "Cập nhật thông tin thành công!";
            }
            break;

        case 'change_password':
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (!password_verify($current_password, $landlord['password'])) {
                $errors[] = "Mật khẩu hiện tại không chính xác";
            }
            if (strlen($new_password) < 6) {
                $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự";
            }
            if ($new_password !== $confirm_password) {
                $errors[] = "Mật khẩu xác nhận không khớp";
            }

            if (empty($errors)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $success = "Đổi mật khẩu thành công!";
            }
            break;
    }
}
?>

<div class="container">
    <div class="row">
        <!-- Thông tin cá nhân -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Thông tin cá nhân</h5>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="mb-3">
                            <label>Tên hiển thị:</label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($landlord['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Email:</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($landlord['email']); ?>" readonly>
                            <small class="text-muted">Email không thể thay đổi</small>
                        </div>
                        
                        <div class="mb-3">
                            <label>Số điện thoại:</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($landlord['phone']); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Đổi mật khẩu</h5>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="mb-3">
                            <label>Mật khẩu hiện tại:</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Mật khẩu mới:</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label>Xác nhận mật khẩu mới:</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cài đặt thông báo -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Cài đặt thông báo</h5>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="emailNotification" checked>
                        <label class="form-check-label" for="emailNotification">
                            Nhận thông báo qua email khi có người quan tâm đến phòng trọ
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="smsNotification">
                        <label class="form-check-label" for="smsNotification">
                            Nhận thông báo qua SMS
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="reviewNotification" checked>
                        <label class="form-check-label" for="reviewNotification">
                            Nhận thông báo khi có đánh giá mới
                        </label>
                    </div>
                    
                    <button class="btn btn-primary save-notifications">Lưu cài đặt</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('.save-notifications').addEventListener('click', function() {
    // TODO: Implement notification settings save functionality
    alert('Đã lưu cài đặt thông báo!');
});
</script>
