<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID flashcard từ URL
$flashcard_id = isset($_GET['flashcard_id']) ? (int)$_GET['flashcard_id'] : 0;

// Kiểm tra xem flashcard có tồn tại
$query = "SELECT * FROM flashcard WHERE flashcard_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $flashcard_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h1>Flashcard không tồn tại!</h1>";
    exit;
}

$flashcard = $result->fetch_assoc();
$vocabularySet_id = $flashcard['vocabularySet_id'];

// Lấy thông tin bộ từ vựng
$query = "SELECT * FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $vocabularySet_id);
$stmt->execute();
$vocabulary_set = $stmt->get_result()->fetch_assoc();

$stmt->close();

// Cập nhật flashcard nếu người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $vocab = $_POST['vocab'];
    $ipa = $_POST['ipa'];
    $meaning = $_POST['meaning'];
    $flashcard_type = $_POST['flashcard_type'];
    $example = $_POST['example'];
    $imagePath = $flashcard['image_path']; // Giữ lại hình ảnh cũ nếu không thay đổi
    if (!empty($_POST['image_url'])) {
        $imagePath = $_POST['image_url']; // Cập nhật với URL mới nếu có
    }
    $query = "UPDATE flashcard SET vocab = ?, ipa = ?, meaning = ?, flashcard_type = ?, example = ?, image_path = ? WHERE flashcard_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssi', $vocab, $ipa, $meaning, $flashcard_type, $example, $imagePath, $flashcard_id);
    $stmt->execute();
    $stmt->close();
    echo "<h3>Flash card đã được cập nhật thành công! </h3>";
    // Chuyển hướng về trang danh sách flashcards
    header("Location: flashcards.php?vocabularySet_id=$vocabularySet_id");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Flashcard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/flashcards.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Sửa Flashcard: <?php echo htmlspecialchars($flashcard['vocab']); ?></h1>
        <div class="card mx-auto shadow-sm" style="max-width: 400px;">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="vocab" class="form-label">Tên Từ Vựng</label>
                        <input type="text" class="form-control" id="vocab" name="vocab" value="<?php echo htmlspecialchars($flashcard['vocab']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ipa" class="form-label">IPA</label>
                        <input type="text" class="form-control" id="ipa" name="ipa" value="<?php echo htmlspecialchars($flashcard['ipa']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="meaning" class="form-label">Nghĩa</label>
                        <textarea class="form-control" id="meaning" name="meaning" required><?php echo htmlspecialchars($flashcard['meaning']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="flashcard_type" class="form-label">Loại Từ</label>
                        <input type="text" class="form-control" id="flashcard_type" name="flashcard_type" value="<?php echo htmlspecialchars($flashcard['flashcard_type']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="example" class="form-label">Ví Dụ</label>
                        <textarea class="form-control" id="example" name="example"><?php echo htmlspecialchars($flashcard['example']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Hình Ảnh</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($flashcard['image_path'] ?? ''); ?>">
                        <?php if (!empty($flashcard['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($flashcard['image_path']); ?>" alt="<?= htmlspecialchars($flashcard['vocab']); ?>" class="img-thumbnail mt-2" style="max-width: 150px;">
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                    <a href="flashcards.php?vocabularySet_id=<?php echo $vocabularySet_id; ?>" class="btn btn-light">Quay Lại</a>
                </form>
            </div>
        </div>
    </div>
    <?php include('../templates/footer.php'); ?>
</body>
</html>
