<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID bộ từ vựng từ URL
$vocabularySet_id = isset($_GET['vocabularySet_id']) ? (int)$_GET['vocabularySet_id'] : 0;

// Kiểm tra xem bộ từ vựng có tồn tại
$query = "SELECT vocabulary_name FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $vocabularySet_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h1>Bộ từ vựng không tồn tại!</h1>";
    exit;
}

$vocabulary_set = $result->fetch_assoc();

// Lấy danh sách các flashcard thuộc bộ từ vựng
$query = "
    SELECT f.flashcard_id,f.vocab, f.meaning 
    FROM flashcard f 
    WHERE f.vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $vocabularySet_id);
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
    <title>Trò Chơi Lật Thẻ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/game.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h3 class="text-center mb-4">Trò Chơi Lật Thẻ - Bộ Từ Vựng: <?php echo htmlspecialchars($vocabulary_set['vocabulary_name']); ?></h3>
        <div class="text-first mt-4">
            <a href="choose_vocabulary.php" class="btn btn-success">Trở về</a>
        </div>
        <div class="game-board">
            <?php while ($row = $flashcards->fetch_assoc()): ?>
                <!-- Thẻ từ tiếng Anh -->
                <div class="card-item" data-flashcard-id="<?php echo $row['flashcard_id']; ?>" data-content="<?php echo htmlspecialchars($row['vocab']); ?>">
                    <div class="card-inner">
                        <div class="card-front"></div> <!-- Mặt trước (ẩn nội dung) -->
                        <div class="card-back"><?php echo htmlspecialchars($row['vocab']); ?></div> <!-- Mặt sau (hiện nội dung khi lật) -->
                    </div>
                </div>
                <!-- Thẻ nghĩa tiếng Việt -->
                <div class="card-item" data-flashcard-id="<?php echo $row['flashcard_id']; ?>" data-content="<?php echo htmlspecialchars($row['meaning']); ?>">
                    <div class="card-inner">
                        <div class="card-front"></div> <!-- Mặt trước (ẩn nội dung) -->
                        <div class="card-back"><?php echo htmlspecialchars($row['meaning']); ?></div> <!-- Mặt sau (hiện nội dung khi lật) -->
                    </div>
                </div>
            <?php endwhile; ?>
        </div>




        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="restartGame()">Chơi lại</button>
        </div>
    </div>
    <?php include('../templates/footer.php'); ?>
    <script src="../js/game.js"></script>
</body>
</html>
