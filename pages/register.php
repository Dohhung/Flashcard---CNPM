<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Lấy tên người dùng
    $email = trim($_POST['email']); // Lấy email
    $phoneNumber = trim($_POST['phoneNumber']); // Lấy số điện thoại
    $password = trim($_POST['password']); // Lấy mật khẩu
    $confirm_password = trim($_POST['confirm_password']); // Lấy mật khẩu xác nhận

    // Kiểm tra nếu mật khẩu và mật khẩu xác nhận không khớp
    if ($password !== $confirm_password) {
        $error_message = "Mật khẩu không khớp!";
    } else {
        // Kiểm tra nếu tên người dùng đã tồn tại
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Tên người dùng đã tồn tại!";
        } else {
            // Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Thực hiện việc thêm người dùng mới vào cơ sở dữ liệu
            $insert_query = "INSERT INTO user (username, email, phoneNumber, password, role) 
                             VALUES (?, ?, ?, ?, 'user')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssss", $username, $email, $phoneNumber, $hashed_password);
            
            if ($stmt->execute()) {
                // Đăng ký thành công, chuyển đến trang đăng nhập
                $_SESSION['username'] = $username;
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Lỗi khi đăng ký tài khoản!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/register.css"> <!-- Liên kết file CSS -->
</head>
<body>
    <div class="register-container">
        <h2>Đăng Ký</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tên tài khoản</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phoneNumber" class="form-label">Số điện thoại</label>
                <input type="text" name="phoneNumber" id="phoneNumber" class="form-control">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng Ký</button>
        </form>
        <div class="mt-3 text-center">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>
