<?php
// Kết nối cơ sở dữ liệu
include('./db.php'); // Đảm bảo rằng file db.php nằm trong cùng thư mục với index.php

// Bắt đầu phiên làm việc
session_start();

// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learn App - Trang chủ</title>
    <link rel="stylesheet" href="./css/style.css"> 
</head>
<body>
    <?php include('./templates/header.php'); ?>

    <div class="home-container">
        <div class="hero-section text-center">
            <h1>Chào mừng đến với E-Learn App</h1>
            <p>Học từ vựng hiệu quả và thú vị qua các bộ flashcard và trò chơi.</p>
            <?php if (!$isLoggedIn): ?>
            <?php else: ?>
                <h3>Chào, <?php echo htmlspecialchars($username); ?>!</h3>
            <?php endif; ?>
        </div>
    </div>

    <div class="features-section container">
        <h2 class="text-center mb-4">Khám phá các tính năng</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card">
                    <img src="./images/vocabulary.png" alt="Vocabulary">
                    <h3>Học từ vựng</h3>
                    <p>Học từ vựng qua các bộ flashcard theo chủ đề.</p>
                    <a href="./pages/vocabulary_sets.php" class="btn btn-outline-primary">Xem chi tiết</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <img src="./images/game.png" alt="Games">
                    <h3>Trò chơi</h3>
                    <p>Thử sức với trò chơi để ghi nhớ từ vựng nhanh.</p>
                    <a href="./pages/choose_vocabulary.php" class="btn btn-outline-primary">Chơi ngay</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <img src="./images/history.png" alt="History">
                    <h3>Lịch sử học tập</h3>
                    <p>Theo dõi tiến trình và kết quả học của bạn tại đây.
                    </p>
                    <a href="./pages/history.php" class="btn btn-outline-primary">Xem lịch sử</a>
                </div>
            </div>
        </div>
    </div>

    <?php include('./templates/footer.php'); ?>
</body>
</html>
