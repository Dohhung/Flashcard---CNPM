<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy danh sách các bộ từ vựng mà người dùng có thể chơi
$user_id = $_SESSION['user_id']; // ID người dùng hiện tại
$query = "
    SELECT vocabularySet_id, vocabulary_name 
    FROM vocabulary_set
    WHERE user_id = ? OR vocabulary_type = 'default'
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id); // Gán user_id hiện tại
$stmt->execute();
$result = $stmt->get_result();

$sets = [];
while ($row = $result->fetch_assoc()) {
    $sets[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Bộ Từ Vựng - Trò Chơi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Chọn Bộ Từ Vựng Để Chơi</h1>
        <form id="vocabularyForm">
            <div class="list-group">
                <?php if (!empty($sets)): ?>
                    <?php foreach ($sets as $set): ?>
                        <div class="list-group-item">
                            <input type="radio" name="vocabularySet_id" value="<?php echo $set['vocabularySet_id']; ?>" id="set_<?php echo $set['vocabularySet_id']; ?>" required>
                            <label for="set_<?php echo $set['vocabularySet_id']; ?>"><?php echo htmlspecialchars($set['vocabulary_name']); ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">Không có bộ từ vựng nào để hiển thị.</p>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary me-2" onclick="redirectToGame('game.php')">Game 1</button>
                <button type="button" class="btn btn-primary me-3" onclick="redirectToGame('game2.php')">Game 2</button>
            </div>
        </form>
    </div>

    <script>
        function redirectToGame(gameUrl) {
            // Lấy form và kiểm tra xem bộ từ vựng đã được chọn chưa
            const form = document.getElementById('vocabularyForm');
            const selectedSet = form.querySelector('input[name="vocabularySet_id"]:checked');

            if (selectedSet) {
                const vocabularySetId = selectedSet.value;
                // Chuyển hướng tới trang game tương ứng với tham số vocabularySet_id
                window.location.href = `${gameUrl}?vocabularySet_id=${vocabularySetId}`;
            } else {
                // Hiển thị cảnh báo nếu chưa chọn bộ từ vựng
                alert('Vui lòng chọn một bộ từ vựng trước khi chọn game!');
            }
        }
    </script>
    <?php include('../templates/footer.php'); ?>
</body>
</html>
