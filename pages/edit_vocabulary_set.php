<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID bộ từ vựng từ URL
$vocabularySet_id = isset($_GET['vocabularySet_id']) ? (int)$_GET['vocabularySet_id'] : 0;

// Lấy thông tin bộ từ vựng
$query = "SELECT * FROM vocabulary_set WHERE vocabularySet_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $vocabularySet_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Bộ từ vựng không tồn tại hoặc bạn không có quyền sửa bộ này.</p>";
    exit();
}

$vocabulary_set = $result->fetch_assoc();
$stmt->close();

// Xử lý khi người dùng cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocabulary_name = $_POST['vocabulary_name'];
    $description = $_POST['description'];

    $update_query = "UPDATE vocabulary_set SET vocabulary_name = ?, description = ? WHERE vocabularySet_id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param('ssi', $vocabulary_name, $description, $vocabularySet_id);
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: vocabulary_sets.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Bộ Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Sửa Bộ Từ Vựng</h1>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="vocabulary_name" class="form-label">Tên Bộ Từ Vựng</label>
                <input type="text" class="form-control" id="vocabulary_name" name="vocabulary_name" value="<?php echo htmlspecialchars($vocabulary_set['vocabulary_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô Tả</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($vocabulary_set['description']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Cập Nhật</button>
        </form>
    </div>

    <?php include('../templates/footer.php'); ?>
</body>
</html>
