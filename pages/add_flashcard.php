<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID bộ từ vựng từ URL
$vocabularySet_id = isset($_GET['vocabularySet_id']) ? (int)$_GET['vocabularySet_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocab = $_POST['vocab'];
    $meaning = $_POST['meaning'];
    $ipa = $_POST['ipa'];
    $image_path = $_FILES['image']['name'];

    // Xử lý upload ảnh
    if (!empty($image_path)) {
        $target_dir = "../images/";
        $target_file = $target_dir . basename($image_path);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    // Thêm từ vựng vào cơ sở dữ liệu
    $query = "
        INSERT INTO flashcard (vocab, meaning, ipa, image_path, vocabularySet_id)
        VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $vocab, $meaning, $ipa, $image_path, $vocabularySet_id);
    $stmt->execute();
    $stmt->close();

    // Quay lại trang flashcards
    header("Location: flashcards.php?vocabularySet_id=$vocabularySet_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Từ Mới</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Thêm Từ Mới</h1>
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label for="vocab" class="form-label">Từ vựng</label>
                <input type="text" class="form-control" id="vocab" name="vocab" required>
            </div>
            <div class="col-md-6">
                <label for="meaning" class="form-label">Nghĩa</label>
                <input type="text" class="form-control" id="meaning" name="meaning" required>
            </div>
            <div class="col-md-6">
                <label for="ipa" class="form-label">IPA</label>
                <input type="text" class="form-control" id="ipa" name="ipa">
            </div>
            <div class="col-md-6">
                <label for="image" class="form-label">Hình ảnh (tuỳ chọn)</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">Thêm Từ</button>
                <a href="flashcards.php?vocabularySet_id=<?php echo $vocabularySet_id; ?>" class="btn btn-secondary">Quay Lại</a>
            </div>

        </form>
    </div>
    <?php include('../templates/footer.php'); ?>
</body>
</html>
