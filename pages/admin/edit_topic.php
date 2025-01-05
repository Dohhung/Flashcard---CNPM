<?php
// edit_topic.php
session_start();
include('../../db.php');

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

// Lấy thông tin chủ đề
$query = "SELECT * FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vocabularySet_id);
$stmt->execute();
$set = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocabulary_name = $_POST['vocabulary_name'];
    $vocabularySet_id = $_GET['vocabularySet_id']; // Đảm bảo ID được lấy từ URL

    // Cập nhật thông tin chủ đề
    $update_query = "UPDATE vocabulary_set SET vocabulary_name = ? WHERE vocabularySet_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $vocabulary_name, $vocabularySet_id); // 's' cho string, 'i' cho integer
    $stmt->execute();
    $stmt->close();

    $message = "Cập nhật chủ đề thành công!";
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Chủ Đề</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Sửa Chủ Đề</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="vocabulary_name" class="form-label">Tên Chủ Đề</label>
                <input type="text" id="vocabulary_name" name="vocabulary_name" class="form-control" value="<?= htmlspecialchars($set['vocabulary_name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
            <a href="list_vocabulary.php" class="btn btn-light back-button">Quay Lại</a>
        <style>
        /* CSS cho nút Quay lại */
        .back-button {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 10px 15px;
        background-color: #f8f9fa;
        border: 1px solid #ccc;
        border-radius: 4px;
        color: #333;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        }

        .back-button:hover {
        background-color: #e2e6ea;
        border-color: #adb5bd;
        }
        </style>
        </form>
    </div>
</body>
</html>
