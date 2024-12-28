<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Lấy tên người dùng
    $password = trim($_POST['password']); // Lấy mật khẩu

    // Kiểm tra kết nối còn hoạt động
    if ($conn) {
        // Truy vấn kiểm tra tài khoản
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                // Kiểm tra mật khẩu
                if (password_verify($password, $user['password'])) {
                    // Lưu thông tin người dùng vào session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Lưu thời gian đăng nhập vào bảng `history`
                    $user_id = $user['user_id'];
                    $insert_history_query = "INSERT INTO history (user_id, activity_date) VALUES (?, NOW())";
                    $history_stmt = $conn->prepare($insert_history_query);
                    $history_stmt->bind_param('i', $user_id);
                    $history_stmt->execute();
                    $history_stmt->close();

                    // Chuyển hướng theo vai trò
                    if ($user['role'] === 'admin') {
                        header('Location: ../pages/admin/admin_dashboard.php'); // Chuyển hướng admin
                    } else {
                        header('Location: ../index.php'); // Chuyển hướng user
                    }
                    exit();
                } else {
                    $error_message = "Sai mật khẩu!";
                }
            } else {
                $error_message = "Tên người dùng không tồn tại!";
            }
        } else {
            $error_message = "Lỗi truy vấn cơ sở dữ liệu!";
        }
    } else {
        $error_message = "Kết nối cơ sở dữ liệu đã bị đóng!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/login.css"> <!-- Liên kết file CSS -->
</head>
<body>
    <div class="login-container">
        <h2>Đăng Nhập</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tên tài khoản</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
        </form>
        <div class="signup-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
</body>
</html>
