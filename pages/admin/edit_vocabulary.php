<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$flashcard_id = $_GET['flashcard_id'] ?? 0;
$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

// Lấy thông tin từ vựng để sửa
$query = "SELECT * FROM flashcard WHERE flashcard_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $flashcard_id);
$stmt->execute();
$flashcard = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $vocab = $_POST['vocab'];
    $ipa = $_POST['ipa'];
    $meaning = $_POST['meaning'];
    $flashcard_type = $_POST['flashcard_type'];
    $example = $_POST['example'];
    $imagepath = $_POST['image_url'];
    // Cập nhật thông tin từ vựng trong cơ sở dữ liệu
    $query = "UPDATE flashcard SET vocab = ?, ipa = ?, meaning = ?, flashcard_type = ?, example = ?, image_path = ? WHERE flashcard_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $vocab, $ipa, $meaning, $flashcard_type, $example, $imagepath, $flashcard_id);
    $stmt->execute();
    $stmt->close();

    // Chuyển hướng về trang view_vocabulary.php
    header("Location: view_vocabulary.php?vocabularySet_id=$vocabularySet_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css_admin/style.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Sửa Từ Vựng</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="vocab" class="form-label">Tên Từ Vựng</label>
            <input type="text" class="form-control" id="vocab" name="vocab" value="<?= htmlspecialchars($flashcard['vocab']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="ipa" class="form-label">IPA</label>
            <input type="text" class="form-control" id="ipa" name="ipa" value="<?= htmlspecialchars($flashcard['ipa']); ?>">
        </div>
        <div class="mb-3">
            <label for="flashcard_type" class="form-label">Loại Từ</label>
            <input type="text" class="form-control" id="flashcard_type" name="flashcard_type" value="<?= htmlspecialchars($flashcard['flashcard_type'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="meaning" class="form-label">Nghĩa</label>
            <input type="text" class="form-control" id="meaning" name="meaning" value="<?= htmlspecialchars($flashcard['meaning'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label for="example" class="form-label">Ví Dụ</label>
            <textarea class="form-control" id="example" name="example"><?= htmlspecialchars($flashcard['example'] ?? ''); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image_url" class="form-label">Hình Ảnh</label>
            <input type="url" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($flashcard['image_path'] ?? ''); ?>">
            <br>
            <?php if (!empty($flashcard['image_path'])): ?>
                <img src="<?= htmlspecialchars($flashcard['image_path']); ?>" alt="<?= htmlspecialchars($flashcard['vocab']); ?>" class="img-fluid" style="max-width: 200px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Cập Nhật Từ Vựng</button>
    </form>
    <br>
</div>
</body>
</html>
