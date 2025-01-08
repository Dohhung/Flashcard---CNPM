<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
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

// Lấy danh sách từ vựng trong chủ đề
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
    <title>Chủ Đề <?= htmlspecialchars($set['vocabulary_name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Chủ Đề: <?= htmlspecialchars($set['vocabulary_name']); ?></h1>
        <ul class="list-group">
            <?php while ($row = $flashcards->fetch_assoc()): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($row['vocab']); ?> - <?= htmlspecialchars($row['meaning']); ?>
                </li>
            <?php endwhile; ?>
        </ul>
        <ul class="list-group">
            <?php foreach ($topics as $topic): ?>
                <li class="list-group-item">
                    <a href="?action=view_vocab&topic_id=<?= $topic['vocabularySet_id']; ?>">
                    <?= htmlspecialchars($topic['vocabulary_name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
