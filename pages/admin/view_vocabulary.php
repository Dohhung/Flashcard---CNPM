<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

// Lấy các giá trị tìm kiếm từ form, nếu có
$search_value = isset($_GET['search_value']) ? $_GET['search_value'] : '';
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'vocab'; // Mặc định tìm kiếm theo Tên từ vựng

// Biến phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;  // Số lượng từ vựng hiển thị trên mỗi trang
$offset = ($page - 1) * $limit;

// Lấy thông tin chủ đề
$query = "SELECT * FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vocabularySet_id);
$stmt->execute();
$set = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Cập nhật câu truy vấn tìm kiếm với điều kiện phù hợp
$search_column = 'vocab'; // Mặc định tìm kiếm theo Tên từ vựng

switch ($search_type) {
    case 'ipa':
        $search_column = 'ipa';
        break;
    case 'flashcard_type':
        $search_column = 'flashcard_type';
        break;
    case 'meaning':
        $search_column = 'meaning';
        break;
}

$query = "SELECT * FROM flashcard 
          WHERE vocabularySet_id = ? 
          AND $search_column LIKE ? 
          LIMIT ?, ?";
$stmt = $conn->prepare($query);

$search_value_term = "%" . $search_value . "%";

$stmt->bind_param("ssii", $vocabularySet_id, $search_value_term, $offset, $limit);
$stmt->execute();
$flashcards = $stmt->get_result();
$stmt->close();

// Lấy tổng số từ vựng để tính số trang
$query = "SELECT COUNT(*) as total FROM flashcard 
          WHERE vocabularySet_id = ? 
          AND $search_column LIKE ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $vocabularySet_id, $search_value_term);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_records = $total_result['total'];
$total_pages = ceil($total_records / $limit);
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
    <link rel="stylesheet" href="../css_admin/style.css">
</head>
<body>
<a href="list_vocabulary.php" class="btn btn-light back-button">Quay Lại</a>
<style>
/* CSS cho nút Quay lại */
.back-button {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 10px 15px;
    background-color: #f8f9fa;
    border: 1px solid #ccc;
    border-radius: 4px;
    color: #333;
    text-decoration: none;
    font-size: 16px;
    font-weight: bold;
}

.back-button:hover {
    background-color: #e2e6ea;
    border-color: #adb5bd;
}
</style>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?= htmlspecialchars($set['vocabulary_name']); ?></h1>
        
        <!-- Form tìm kiếm -->
        <form class="d-flex mb-4" method="GET" action="view_vocabulary.php">
            <input type="hidden" name="vocabularySet_id" value="<?= $vocabularySet_id; ?>">

            <!-- Dropdown chọn tìm kiếm theo Tên từ vựng, Loại từ, Nghĩa -->
            <select class="form-select me-2" name="search_type" style="width: 150px;">
                <option value="vocab" <?= $search_type == 'vocab' ? 'selected' : ''; ?>>Tên Từ Vựng</option>
                <option value="flashcard_type" <?= $search_type == 'flashcard_type' ? 'selected' : ''; ?>>Loại Từ</option>
                <option value="meaning" <?= $search_type == 'meaning' ? 'selected' : ''; ?>>Nghĩa</option>
            </select>

            <!-- Ô tìm kiếm -->
            <input class="form-control me-2" type="search" name="search_value" placeholder="Nhập từ khóa tìm kiếm..." value="<?= htmlspecialchars($search_value); ?>">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên Từ Vựng</th>
                    <th>IPA</th>
                    <th>Loại Từ</th>
                    <th>Nghĩa</th>
                    <th>Ví Dụ</th>
                    <th>Hình Ảnh</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $flashcards->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['vocab']); ?></td>
                        <td><?= htmlspecialchars($row['ipa']); ?></td>
                        <td><?= htmlspecialchars($row['flashcard_type'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['meaning']); ?></td>
                        <td><?= htmlspecialchars($row['example'] ?? ''); ?></td>
                        <td>
                            <?php if (!empty($row['image_path'])): ?>
                                <img src="../../images/<?= htmlspecialchars($row['image_path']); ?>" alt="<?= htmlspecialchars($row['vocab']); ?>" class="img-fluid" style="max-width: 100px;">
                            <?php else: ?>
                                <p>Không có hình ảnh</p>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a href="edit_vocabulary.php?flashcard_id=<?= $row['flashcard_id']; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            <a href="delete_vocabulary.php?flashcard_id=<?= $row['flashcard_id']; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa từ vựng này?');">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="view_vocabulary.php?page=1&search_value=<?= htmlspecialchars($search_value); ?>&search_type=<?= $search_type; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="view_vocabulary.php?page=<?= $page - 1; ?>&search_value=<?= htmlspecialchars($search_value); ?>&search_type=<?= $search_type; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>">Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="view_vocabulary.php?page=<?= $i; ?>&search_value=<?= htmlspecialchars($search_value); ?>&search_type=<?= $search_type; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="view_vocabulary.php?page=<?= $page + 1; ?>&search_value=<?= htmlspecialchars($search_value); ?>&search_type=<?= $search_type; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>">Sau</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="view_vocabulary.php?page=<?= $total_pages; ?>&search_value=<?= htmlspecialchars($search_value); ?>&search_type=<?= $search_type; ?>&vocabularySet_id=<?= $vocabularySet_id; ?>">Cuối</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <a href="add_vocabulary.php?vocabularySet_id=<?= $vocabularySet_id; ?>" class="btn btn-success">Thêm Từ Vựng Mới</a>
        <br><br>
    </div>
</body>
</html>
