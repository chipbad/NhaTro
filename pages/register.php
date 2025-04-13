<?php
if(isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = cleanInput($_POST['phone']);
    $role = cleanInput($_POST['role']);
    $errors = [];

    // Validate input
    if (strlen($username) < 3) {
        $errors[] = "Tên người dùng phải có ít nhất 3 ký tự";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không hợp lệ";
    }

    if (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Mật khẩu xác nhận không khớp";
    }

    if (!in_array($role, ['student', 'landlord'])) {
        $errors[] = "Vai trò không hợp lệ";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email đã được sử dụng";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, phone, role, status) 
                VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$username, $email, $hashed_password, $phone, $role])) {
            $_SESSION['success_message'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            redirect('?page=login');
        } else {
            $errors[] = "Có lỗi xảy ra, vui lòng thử lại sau.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center mb-4">Đăng ký tài khoản</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label>Tên người dùng:</label>
                <input type="text" name="username" class="form-control" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="mb-3">
                <label>Số điện thoại:</label>
                <input type="tel" name="phone" class="form-control" required
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="mb-3">
                <label>Mật khẩu:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Xác nhận mật khẩu:</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label>Bạn là:</label>
                <select name="role" class="form-control" required>
                    <option value="student">Sinh viên (Người thuê)</option>
                    <option value="landlord">Chủ trọ</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Đăng ký</button>
            
            <p class="mt-3">
                Đã có tài khoản? <a href="?page=login">Đăng nhập ngay</a>
            </p>
        </form>
    </div>
</div>
