<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Lấy danh sách người dùng có role là "user"
$query = "
    SELECT u.user_id, u.username, u.email, 
           COALESCE(SUM(TIME_TO_SEC(h.duration)) / 60, 0) AS total_minutes
    FROM user u
    LEFT JOIN history h ON u.user_id = h.user_id
    WHERE u.role = 'user'
";
if (!empty($search)) {
    $query .= " AND u.username LIKE CONCAT('%', ?, '%')";
}
$query .= " GROUP BY u.user_id ORDER BY total_minutes DESC";

$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bind_param('s', $search);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo Dõi Tiến Trình</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Theo Dõi Tiến Trình Người Học</h1>

        <!-- Form tìm kiếm -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Nhập tên người học" value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">Tìm Kiếm</button>
            </div>
        </form>

        <!-- Bảng danh sách người dùng -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên Người Học</th>
                        <th>Email</th>
                        <th>Thời Gian Học (phút)</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= round($user['total_minutes'], 2) ?></td>
                                <td>
                                    <a href="progress_detail.php?user_id=<?= $user['user_id'] ?>" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Không có dữ liệu</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Nút quay lại -->
        <div class="text-center mt-4">
            <a href="admin_dashboard.php" class="btn btn-secondary">Quay Lại</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
