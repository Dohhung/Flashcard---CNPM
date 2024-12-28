<?php
session_start();
//include('../db.php');
include('../../db.php');
// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocab = $_POST['vocab'];
    $meaning = $_POST['meaning'];
    $query = "INSERT INTO flashcard (vocabularySet_id, vocab, meaning) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $vocabularySet_id, $vocab, $meaning);
    $stmt->execute();
    $stmt->close();
    $message = "Thêm từ vựng thành công!";
}

// Lấy thông tin chủ đề
$query = "SELECT * FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vocabularySet_id);
$stmt->execute();
$set = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy danh sách từ vựng
$query = "SELECT * FROM flashcard WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vocabularySet_id);
$stmt->execute();
$flashcards = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thao Tác Với Chủ Đề: <?= htmlspecialchars($set['vocabulary_name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Thao Tác Với Chủ Đề: <?= htmlspecialchars($set['vocabulary_name']); ?></h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>Danh Sách Từ Vựng</h2>
        <ul class="list-group mb-4">
            <?php while ($row = $flashcards->fetch_assoc()): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($row['vocab']); ?> - <?= htmlspecialchars($row['meaning']); ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <h2>Thêm Từ Vựng</h2>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="vocab" class="form-label">Từ Vựng</label>
                <input type="text" id="vocab" name="vocab" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="meaning" class="form-label">Nghĩa</label>
                <input type="text" id="meaning" name="meaning" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Thêm</button>
        </form>
    </div>
</body>
</html>
