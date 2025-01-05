<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

// Thiết lập phân trang
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = $search ? "AND (u.username LIKE ? OR u.email LIKE ?)" : '';

// Đếm tổng số bản ghi trong lịch sử, không bao gồm admin
$count_query = "SELECT COUNT(*) AS total 
                FROM history h 
                JOIN user u ON h.user_id = u.user_id 
                WHERE u.role != 'admin' $search_query";
$stmt = $conn->prepare($count_query);
if ($search) {
    $like_search = "%$search%";
    $stmt->bind_param('ss', $like_search, $like_search);
}
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Lấy lịch sử học tập, không bao gồm admin
$query = "SELECT h.history_id, u.user_id, u.username, u.email, h.activity_date, h.duration 
          FROM history h 
          JOIN user u ON h.user_id = u.user_id 
          WHERE u.role != 'admin' $search_query 
          ORDER BY h.activity_date DESC 
          LIMIT ?, ?";
$stmt = $conn->prepare($query);
if ($search) {
    $stmt->bind_param('ssii', $like_search, $like_search, $offset, $items_per_page);
} else {
    $stmt->bind_param('ii', $offset, $items_per_page);
}
$stmt->execute();
$history_data = $stmt->get_result();
$stmt->close();

// Tính tổng số trang
$total_pages = ceil($total_records / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Học Tập Người Dùng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <a href="/FC/pages/admin/admin_dashboard.php" class="btn btn-secondary mb-3">Quay Lại</a>
        <h1 class="text-center mb-4">Lịch Sử Học Tập Người Dùng</h1>

        <!-- Form tìm kiếm -->
        <form class="d-flex mb-4" method="GET" action="">
            <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm theo tên hoặc email..." value="<?= htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </form>

        <!-- Bảng lịch sử học tập -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Người Dùng</th>
                    <th>Email</th>
                    <th>Ngày Hoạt Động</th>
                    <th>Thời Gian Học</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($history_data->num_rows > 0): ?>
                    <?php $index = ($page - 1) * $items_per_page + 1; ?>
                    <?php while ($history = $history_data->fetch_assoc()): ?>
                        <tr>
                            <td><?= $index++; ?></td>
                            <td><?= htmlspecialchars($history['username']); ?></td>
                            <td><?= htmlspecialchars($history['email']); ?></td>
                            <td><?= htmlspecialchars($history['activity_date']); ?></td>
                            <td><?= htmlspecialchars($history['duration']); ?></td>
                            <td>
                                <a href="/FC/pages/admin/history_user_detail.php?user_id=<?= $history['user_id']; ?>" class="btn btn-info btn-sm">Xem chi tiết</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Không tìm thấy lịch sử học tập.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>
</html>
