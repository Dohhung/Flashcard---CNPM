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
$search_query = $search ? "AND (username LIKE ? OR email LIKE ?)" : '';

// Đếm tổng số người dùng
$count_query = "SELECT COUNT(*) AS total FROM user WHERE role != 'admin' $search_query";
$stmt = $conn->prepare($count_query);
if ($search) {
    $like_search = "%$search%";
    $stmt->bind_param('ss', $like_search, $like_search);
}
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Lấy danh sách người dùng
$user_query = "SELECT user_id, username, email, phoneNumber, dateSignin, time_login, time_logout FROM user WHERE role != 'admin' $search_query LIMIT ?, ?";
$stmt = $conn->prepare($user_query);
if ($search) {
    $stmt->bind_param('ssii', $like_search, $like_search, $offset, $items_per_page);
} else {
    $stmt->bind_param('ii', $offset, $items_per_page);
}
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

// Tính tổng số trang
$total_pages = ceil($total_users / $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
    <a href="/FC/pages/admin/admin_dashboard.php" class="btn btn-secondary mt-3">Quay Lại</a>
        <h1 class="text-center mb-4">Quản Lý Người Dùng</h1>
        
        <!-- Form tìm kiếm -->
        <form class="d-flex mb-4" method="GET" action="">
            <input class="form-control me-2" type="search" name="search" placeholder="Tìm kiếm người dùng..." value="<?= htmlspecialchars($search); ?>">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </form>

        <!-- Bảng danh sách người dùng -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên Người Dùng</th>
                    <th>Email</th>
                    <th>Số Điện Thoại</th>
                    <th>Ngày Đăng Ký</th>
                    <th>Thời Gian Đăng Nhập</th>
                    <th>Thời Gian Đăng Xuất</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users->num_rows > 0): ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id']); ?></td>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['phoneNumber']); ?></td>
                            <td><?= htmlspecialchars($user['dateSignin']); ?></td>
                            <td><?= htmlspecialchars($user['time_login']); ?></td>
                            <td><?= htmlspecialchars($user['time_logout']); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $user['user_id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <a href="delete_user.php?id=<?= $user['user_id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?');" class="btn btn-sm btn-danger">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Không tìm thấy người dùng nào.</td>
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
