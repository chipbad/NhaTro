<?php
// Xóa tất cả các biến session
session_unset();

// Hủy phiên session
session_destroy();

// Chuyển hướng về trang chủ
redirect('index.php');
?>
