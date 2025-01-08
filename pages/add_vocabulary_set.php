<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Xử lý khi người dùng gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocabulary_name = $_POST['vocabulary_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session

    // Kiểm tra tên bộ từ vựng đã tồn tại
    $check_query = "SELECT COUNT(*) AS count FROM vocabulary_set WHERE vocabulary_name = ? AND user_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('si', $vocabulary_name, $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $error_message = "Bộ từ vựng đã tồn tại. Vui lòng đặt tên khác!";
    } else {
        if (!empty($vocabulary_name)) {
            try {
                // Thêm dữ liệu
                $query = "
                    INSERT INTO vocabulary_set (user_id, vocabulary_name, description, vocabulary_type)
                    VALUES (?, ?, ?, 'personal')
                ";
                $stmt = $conn->prepare($query);
                $stmt->bind_param('iss', $user_id, $vocabulary_name, $description);
                $stmt->execute();
                $stmt->close();
    
                header('Location: vocabulary_sets.php');
                exit();
            } catch (mysqli_sql_exception $e) {
                $error_message = "Lỗi khi thêm bộ từ vựng: " . $e->getMessage();
            }
        } else {
            $error_message = "Tên bộ từ vựng không được để trống!";
        }
    }
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;  // Số lượng bộ từ vựng trên mỗi trang
$offset = ($page - 1) * $limit;

// Lấy danh sách bộ từ vựng
$query = "SELECT * FROM vocabulary_set WHERE user_id = ? LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $user_id, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Tính số trang
$total_query = "SELECT COUNT(*) AS total FROM vocabulary_set WHERE user_id = ?";
$stmt = $conn->prepare($total_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$total_result = $stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Đóng kết nối
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Bộ Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        // Hàm ẩn thông báo sau 3 giây
        setTimeout(function() {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 3000);
    </script>
</head>
<body>
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Thêm Bộ Từ Vựng</h1>

        <!-- Form thêm bộ từ vựng -->
        <form action="add_vocabulary_set.php" method="POST" class="row g-3">
            <div class="col-md-6 offset-md-3">
                <label for="vocabulary_name" class="form-label">Tên Bộ Từ Vựng</label>
                <input type="text" id="vocabulary_name" name="vocabulary_name" class="form-control" required>
            </div>
            <div class="col-md-6 offset-md-3">
                <label for="description" class="form-label">Mô Tả</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>
            <div class="col-md-6 offset-md-3 text-end">
                <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="vocabulary_sets.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>

        <?php if (!empty($error_message)): ?>
            <div id="error-message" class="text-danger mt-3 text-center">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Phân trang -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="add_vocabulary_set.php?page=<?php echo $i; ?>" class="btn btn-secondary btn-sm"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>

    <?php include('../templates/footer.php'); ?>
</body>
</html>
