<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Điều hướng đến trang đăng nhập nếu chưa đăng nhập
    exit();
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Xử lý form cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $password = $_POST['password'];
    $newPassword = $_POST['newPassword'];

    // Kiểm tra nếu có thay đổi mật khẩu
    if (!empty($newPassword)) {
        // Cập nhật mật khẩu mới
        $password = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Cập nhật thông tin vào cơ sở dữ liệu
    $updateQuery = "UPDATE user SET username = ?, email = ?, phoneNumber = ?, password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ssssi', $username, $email, $phoneNumber, $password, $user_id);
    if ($stmt->execute()) {
        $message = "Cập nhật thông tin thành công!";
    } else {
        $message = "Có lỗi xảy ra trong quá trình cập nhật.";
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Cá Nhân</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Thông Tin Cá Nhân</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?= $message; ?></div>
        <?php endif; ?>

        <!-- Hiển thị thông tin người dùng -->
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tài Khoản</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phoneNumber" class="form-label">Số Điện Thoại</label>
                <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="<?= htmlspecialchars($user['phoneNumber']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật Khẩu Cũ</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu cũ" required>
            </div>

            <div class="mb-3">
                <label for="newPassword" class="form-label">Mật Khẩu Mới</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Nhập mật khẩu mới (nếu thay đổi)">
            </div>

            <button type="submit" class="btn btn-primary">Cập Nhật Thông Tin</button>
        </form>

        <br>
        <a href="../index.php" class="btn btn-secondary">Trang Chủ</a>
    </div>
</body>
</html>
