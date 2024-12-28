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
    SELECT vocabularySet_id, vocabulary_name, description
    FROM vocabulary_set
    WHERE user_id = ? OR vocabulary_type = 'default'
    ORDER BY user_id DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id); // Gán user_id hiện tại
$stmt->execute();
$result = $stmt->get_result();

// Đóng kết nối
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bộ Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/vocabularysets.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Bộ Từ Vựng</h1>
        
        <!-- Nút thêm bộ từ vựng -->
        <div class="text-end mb-4">
            <a href="add_vocabulary_set.php" class="btn btn-success">Thêm Bộ Từ Vựng</a>
        </div>

        <!-- Danh sách các bộ từ vựng -->
        <div class="row g-3">
        <?php 
        if ($result->num_rows > 0): // Kiểm tra nếu có dữ liệu trả về
            while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($row['vocabulary_name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($row['description']); ?></p>
                            <a href="flashcards.php?vocabularySet_id=<?php echo $row['vocabularySet_id']; ?>" class="btn btn-primary w-100">
                                Chọn Bộ Từ Vựng
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
            endwhile; 
        else: // Nếu không có dữ liệu, hiển thị thông báo
        ?>
            <p class="text-center">Không có bộ từ vựng nào để hiển thị.</p>
        <?php endif; ?>
        </div>
    </div>
    <?php include('../templates/footer.php'); ?>
</body>
</html>

