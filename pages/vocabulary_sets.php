<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // ID người dùng hiện tại

// Định nghĩa số lượng bộ từ vựng trên mỗi trang
$items_per_page = 6; 

// Lấy trang hiện tại từ query parameter (mặc định là trang 1)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Tính toán OFFSET (vị trí bắt đầu của dữ liệu)
$offset = ($current_page - 1) * $items_per_page;

// Lấy tổng số bộ từ vựng để tính số trang
$query = "SELECT COUNT(*) AS total FROM vocabulary_set WHERE user_id = ? OR vocabulary_type = 'default'";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_items = $result->fetch_assoc()['total'];

// Tính toán tổng số trang
$total_pages = ceil($total_items / $items_per_page);

// Lấy danh sách các bộ từ vựng cho trang hiện tại
$query = "
    SELECT vocabularySet_id, vocabulary_name, description, vocabulary_type, user_id
    FROM vocabulary_set
    WHERE user_id = ? OR vocabulary_type = 'default'
    ORDER BY user_id DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $user_id, $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Đóng kết nối sau khi lấy danh sách bộ từ vựng
$stmt->close();
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
                            <h5 class="card-title text-primary">
                                <?php echo htmlspecialchars($row['vocabulary_name']); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </p>
                            <a href="flashcards.php?vocabularySet_id=<?php echo $row['vocabularySet_id']; ?>" class="btn btn-primary w-100">
                                Chọn Bộ Từ Vựng
                            </a>

                            <!-- Nút ba chấm -->
                            <?php if ($row['vocabulary_type'] == 'personal' && $row['user_id'] == $_SESSION['user_id']): ?>
                                <div class="dropdown text-end mt-3">
                                    <button class="ellipsis-btn btn btn-link text-decoration-none p-0" type="button" id="dropdownMenuButton<?php echo $row['vocabularySet_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span>⋮</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $row['vocabularySet_id']; ?>">
                                        <li>
                                            <a class="dropdown-item" href="edit_vocabulary_set.php?vocabularySet_id=<?php echo $row['vocabularySet_id']; ?>">Sửa</a>
                                        </li>
                                        <li>
                                            <form action="delete_vocabulary_set.php" method="POST" style="display: inline;">
                                                <input type="hidden" name="vocabularySet_id" value="<?php echo $row['vocabularySet_id']; ?>">
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirmDelete(<?php echo $has_flashcards ? 'true' : 'false'; ?>);">
                                                    Xóa
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
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

        <!-- Phân trang -->
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?php echo ($current_page == 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($current_page == $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

    </div>
    <?php include('../templates/footer.php'); ?>

    <script>
        function confirmDelete(hasFlashcards) {
            if (hasFlashcards) {
                const userConfirmed = confirm("Bộ từ vựng này còn từ vựng. Bạn vẫn muốn xóa không?");
                return userConfirmed; 
            }
            return true; 
        }
    </script>
</body>
</html>
