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

// Xử lý khi người dùng submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocab = $_POST['vocab'] ?? '';
    $meaning = $_POST['meaning'] ?? '';
    $ipa = $_POST['ipa'] ?? '';

    // Cập nhật flashcard
    $update_query = "UPDATE flashcard SET vocab = ?, meaning = ?, ipa = ? WHERE flashcard_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param('sssi', $vocab, $meaning, $ipa, $flashcard_id);

    if ($update_stmt->execute()) {
        header("Location: flashcards.php?vocabularySet_id=" . $flashcard['vocabularySet_id']);
        exit();
    } else {
        echo "<h1>Có lỗi xảy ra khi cập nhật flashcard!</h1>";
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Flashcard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Sửa Flashcard</h1>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="vocab" class="form-label">Từ Vựng</label>
                <input type="text" name="vocab" id="vocab" class="form-control" value="<?php echo htmlspecialchars($flashcard['vocab']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="meaning" class="form-label">Nghĩa</label>
                <input type="text" name="meaning" id="meaning" class="form-control" value="<?php echo htmlspecialchars($flashcard['meaning']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="ipa" class="form-label">Phát Âm (IPA)</label>
                <input type="text" name="ipa" id="ipa" class="form-control" value="<?php echo htmlspecialchars($flashcard['ipa']); ?>">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
                <a href="flashcards.php?vocabularySet_id=<?php echo $flashcard['vocabularySet_id']; ?>" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
