<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

// Truy vấn để lấy thông tin chủ đề
$query = "SELECT vocabulary_name FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vocabularySet_id);
$stmt->execute();
$set = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Nếu không tìm thấy chủ đề, chuyển hướng về trang danh sách chủ đề
if (!$set) {
    header('Location: flashcards.php');
    exit();
}
// Xử lý thêm từng từ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_single_word'])) {
    // Lấy dữ liệu từ form
    $vocab = trim($_POST['vocab']);
    $ipa = $_POST['ipa'];
    $meaning = $_POST['meaning'];
    $flashcard_type = $_POST['flashcard_type'];
    $example = $_POST['example'];
    $image_Path =$_POST['image_url'];
    // Ràng buộc toàn vẹn: Kiểm tra xem các trường bắt buộc có được nhập đầy đủ không
    if (empty($vocab) || empty($ipa) || empty($meaning)) {
        $error_message = "Vui lòng điền đầy đủ các trường: Từ vựng, IPA và Nghĩa.";
    } else {
        $query = "SELECT * FROM flashcard WHERE vocabularySet_id = ? AND vocab = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $vocabularySet_id, $vocab);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Từ vựng này đã tồn tại trong bộ từ vựng.";
        } else {
            $query = "INSERT INTO flashcard (vocabularySet_id, image_path, vocab, ipa, meaning, flashcard_type, example) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssss", $vocabularySet_id, $image_Path, $vocab, $ipa, $meaning, $flashcard_type, $example);

            if ($stmt->execute()) {
                $success_message = "Đã thêm từ vựng mới thành công!";
            } else {
                $error_message = "Có lỗi xảy ra khi thêm từ vựng. Vui lòng thử lại.";
            }

            $stmt->close();
        }
    }
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
        <a href="vocabulary_sets.php" class="btn btn-success">Trở về Bộ Từ Vựng</a>
        <h1 class="text-center mb-4">Thêm Từ Mới</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#singleWord">Thêm Từng Từ</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <div id="singleWord" class="tab-pane fade show active">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="vocab" class="form-label">Từ vựng</label>
                            <input type="text" class="form-control" id="vocab" name="vocab" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ipa" class="form-label">IPA</label>
                            <input type="text" class="form-control" id="ipa" name="ipa" required>
                        </div>
                        <div class="col-md-6">
                            <label for="meaning" class="form-label">Nghĩa</label>
                            <input type="text" class="form-control" id="meaning" name="meaning" required>
                        </div>
                        <div class="col-md-6">
                            <label for="flashcard_type" class="form-label">Loại Từ</label>
                            <input type="text" class="form-control" id="flashcard_type" name="flashcard_type" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="example" class="form-label">Ví Dụ</label>
                        <textarea class="form-control" id="example" name="example" required></textarea>
                    </div>
                    <div class="col-md-">
                        <label for="image_url" class="form-label">URL Hình Ảnh</label>
                        <input type="url" class="form-control" id="image_url" name="image_url">
                    </div>
                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary" name="add_single_word">Thêm Từ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('../templates/footer.php'); ?>
</body>
</html>
