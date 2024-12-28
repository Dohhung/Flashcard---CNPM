<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Xử lý khi người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocabulary_name = $_POST['vocabulary_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session

    if (!empty($vocabulary_name)) {
        // Thêm vào cơ sở dữ liệu
        $query = "
            INSERT INTO vocabulary_set (user_id, vocabulary_name, description, vocabulary_type)
            VALUES (?, ?, ?, 'personal')
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iss', $user_id, $vocabulary_name, $description);
        $stmt->execute();
        $stmt->close();

        // Chuyển hướng lại về trang danh sách
        header('Location: vocabulary_sets.php');
        exit();
    } else {
        $error_message = "Tên bộ từ vựng không được để trống!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Bộ Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Thêm Bộ Từ Vựng</h1>
        
        <!-- Form thêm bộ từ vựng -->
        <form action="add_vocabulary_set.php" method="POST" class="row g-3">
            <div class="col-md-6 offset-md-3">
                <label for="vocabulary_name" class="form-label">Tên Bộ Từ Vựng</label>
                <input type="text" id="vocabulary_name" name="vocabulary_name" class="form-control" required>
            </div>
            <div class="col-md-6 offset-md-3">
                <label for="description" class="form-label">Mô Tả</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="col-md-6 offset-md-3 text-end">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="vocabulary_sets.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>

        <?php if (!empty($error_message)): ?>
            <p class="text-danger mt-3 text-center"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>
    <?php include('../templates/footer.php'); ?>
</body>
</html>
