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

// Xóa flashcard khi người dùng xác nhận
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_query = "DELETE FROM flashcard WHERE flashcard_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param('i', $flashcard_id);

    if ($delete_stmt->execute()) {
        header("Location: flashcards.php?vocabularySet_id=" . $flashcard['vocabularySet_id']);
        exit();
    } else {
        echo "<h1>Có lỗi xảy ra khi xóa flashcard!</h1>";
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
    <title>Xóa Flashcard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Xóa Flashcard</h1>
        <p class="text-center">Bạn có chắc chắn muốn xóa flashcard: <strong><?php echo htmlspecialchars($flashcard['vocab']); ?></strong>?</p>

        <form action="" method="POST" class="text-center">
            <button type="submit" class="btn btn-danger">Xóa</button>
            <a href="flashcards.php?vocabularySet_id=<?php echo $flashcard['vocabularySet_id']; ?>" class="btn btn-secondary">Hủy</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
