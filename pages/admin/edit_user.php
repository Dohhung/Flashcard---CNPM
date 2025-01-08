<?php
session_start();
include('../../db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phoneNumber'];

        $stmt = $conn->prepare("UPDATE user SET username = ?, email = ?, phoneNumber = ? WHERE user_id = ?");
        $stmt->bind_param('sssi', $username, $email, $phoneNumber, $user_id);
        $stmt->execute();
        $stmt->close();

        header('Location: manage_user.php');
        exit();
    }
} else {
    header('Location: manage_user.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Người Dùng</title>
    <link rel="stylesheet" href="../../css_admin/edit_user.css">

</head>
<body>
    <form method="POST" action="">
        <label>Tên Người Dùng:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
        <label>Số Điện Thoại:</label>
        <input type="text" name="phoneNumber" value="<?= htmlspecialchars($user['phoneNumber']); ?>" required>
        <button type="submit">Cập Nhật</button>
    </form>
</body>
</html>
