<?php
session_start();

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php"); // Chuyển hướng về trang đăng nhập
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../css_admin/admin_dashboard.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center text-light">Admin Panel</h4>
        <a href="/FC/index.php"><i class="fas fa-home"></i> Trang Chủ</a>
        <a href="list_vocabulary.php"><i class="fas fa-book"></i> Bộ Từ Vựng</a>
        <a href="reminder.php"><i class="fas fa-bell"></i> Nhắc Nhở</a>
        <a href="progress.php"><i class="fas fa-chart-line"></i> Theo Dõi Tiến Độ</a>
        <a href="/FC/pages/login.php"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
    </div>

    <!-- Main content -->
    <div class="content">
        <!-- Navbar -->
        <div class="navbar">
            <h1>Admin Dashboard</h1>
        </div>

        <!-- Welcome message -->
        <div class="welcome">
            Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
