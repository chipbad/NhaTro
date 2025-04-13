<?php
// Xử lý các hành động
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    // Không cho phép thao tác với tài khoản admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_role = $stmt->fetchColumn();
    
    if ($user_role != 'admin') {
        switch($action) {
            case 'ban':
                $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                $stmt->execute([$user_id]);
                break;
                
            case 'activate':
                $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                $stmt->execute([$user_id]);
                break;
                
            case 'delete':
                // Xóa các reviews của user
                $stmt = $conn->prepare("DELETE FROM reviews WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Xóa các phòng nếu là chủ trọ
                if ($user_role == 'landlord') {
                    $stmt = $conn->prepare("DELETE FROM amenities WHERE room_id IN 
                                         (SELECT id FROM rooms WHERE landlord_id = ?)");
                    $stmt->execute([$user_id]);
                    
                    $stmt = $conn->prepare("DELETE FROM reviews WHERE room_id IN 
                                         (SELECT id FROM rooms WHERE landlord_id = ?)");
                    $stmt->execute([$user_id]);
                    
                    $stmt = $conn->prepare("DELETE FROM rooms WHERE landlord_id = ?");
                    $stmt->execute([$user_id]);
                }
                
                // Cuối cùng xóa user
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                break;
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title">Quản lý người dùng</h5>
                <input type="text" class="form-control admin-search" style="width: 300px" 
                       placeholder="Tìm kiếm người dùng...">
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên người dùng</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày đăng ký</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users ORDER BY created_at DESC";
                        $stmt = $conn->query($sql);
                        while ($user = $stmt->fetch()) {
                            ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo $user['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td class="action-buttons">
                                    <?php if ($user['role'] != 'admin'): ?>
                                        <?php if ($user['status'] == 'active'): ?>
                                            <form method="POST" style="display: inline-block">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="ban">
                                                <button type="submit" class="btn btn-sm btn-warning" 
                                                        data-confirm="Bạn có chắc muốn khóa tài khoản này?">
                                                    Khóa
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline-block">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        data-confirm="Bạn có chắc muốn mở khóa tài khoản này?">
                                                    Mở khóa
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display: inline-block">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    data-confirm="Bạn có chắc muốn xóa người dùng này? Mọi dữ liệu liên quan sẽ bị xóa!">
                                                Xóa
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
