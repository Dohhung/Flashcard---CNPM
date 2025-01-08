<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php"); // Chuyển hướng về trang đăng nhập
    exit();
}

// Biến lưu thông báo
$message = "";

// Lấy trang hiện tại, mặc định là trang 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;  // Số lượng chủ đề hiển thị trên mỗi trang
$offset = ($page - 1) * $limit;

// Lấy giá trị tìm kiếm nếu có
$search = isset($_POST['search']) ? $conn->real_escape_string($_POST['search']) : '';

// Truy vấn để lấy danh sách chủ đề có phân trang và tìm kiếm
$query = "SELECT * FROM vocabulary_set WHERE vocabulary_name LIKE '%$search%' LIMIT $offset, $limit";
$result = $conn->query($query);

// Lấy tổng số chủ đề để tính số trang
$total_query = "SELECT COUNT(*) as total FROM vocabulary_set WHERE vocabulary_name LIKE '%$search%'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Xử lý thêm chủ đề mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_topic'])) {
    $vocabulary_name = $conn->real_escape_string($_POST['vocabulary_name']);
    $description = $conn->real_escape_string($_POST['description']); // Lấy mô tả từ form
    $user_id = 1; // Giả sử admin có user_id là 1

    // Kiểm tra xem chủ đề đã tồn tại chưa
    $check_query = "SELECT COUNT(*) as count FROM vocabulary_set WHERE vocabulary_name = '$vocabulary_name'";
    $check_result = $conn->query($check_query);
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        $message = "Chủ đề này đã tồn tại!";
    } else {
        $insert_query = "INSERT INTO vocabulary_set (user_id, vocabulary_name, vocabulary_type, description) 
                         VALUES ('$user_id', '$vocabulary_name', 'default', '$description')";

        if ($conn->query($insert_query)) {
            $message = "Chủ đề mới đã được thêm thành công!";
        } else {
            $message = "Thêm chủ đề thất bại: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chủ đề</title>
    <link rel="stylesheet" href="../../css_admin/list_vocabulary.css">
    <style>
        /* Style cho nút Quay lại */
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

        /* Style cho thanh tìm kiếm và nút Tìm kiếm */
        .search-container {
            display: flex; /* Đặt các phần tử cùng dòng */
            justify-content: center; /* Canh giữa các phần tử */
            align-items: center; /* Canh chỉnh theo chiều dọc */
            margin-bottom: 20px;
        }

        .search-container input[type="text"] {
            padding: 5px;
            font-size: 14px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-container button {
            padding: 5px 10px;
            font-size: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            margin-left: 10px;
        }

        .search-container button:hover {
            background-color: #0056b3;
        }

        .clear-search {
            position: absolute;
            right: 10px;
            top: 5px;
            cursor: pointer;
            font-size: 16px;
            color: #999;
        }

        .clear-search:hover {
            color: #333;
        }

        /* Style cho phân trang */
        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            padding: 5px 10px;
            margin: 0 5px;
            text-decoration: none;
            background-color: #f1f1f1;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        /* Modal style */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-button-container {
            text-align: center;
            margin-top: 20px;
        }

        .modal-button-container button {
            padding: 10px 20px;
            font-size: 16px;
            margin: 10px;
        }
    </style>
</head>
<body>
<a href="admin_dashboard.php" class="btn btn-light back-button">Quay Lại</a>

<h1>Quản lý Chủ đề</h1>

<!-- Hiển thị thông báo nếu có -->
<?php if (!empty($message)): ?>
    <p id="alert-message" style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>


<!-- Form thêm chủ đề -->
<div class="form-container">
    <form method="POST">
        <h2>Thêm Chủ đề mới</h2>
        <label for="vocabulary_name">Tên Chủ đề:</label>
        <input type="text" name="vocabulary_name" id="vocabulary_name" required>
        <label for="description">Mô tả:</label>
        <input type="text" name="description" id="description" required>
        <br>
        <button type="submit" name="add_topic">Thêm Chủ đề</button>
    </form>
</div>

<!-- Danh sách chủ đề -->
<h2>Danh sách Chủ đề</h2>
<!-- Form tìm kiếm -->
<div class="search-container">
    <form method="POST" action="list_vocabulary.php">
        <input type="text" name="search" placeholder="Tìm kiếm chủ đề..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Tìm kiếm</button>
        <!-- Dấu X để xóa tìm kiếm -->
        <?php if ($search): ?>
            <span class="clear-search" onclick="clearSearch()">&#x2716;</span>
        <?php endif; ?>
    </form>
</div>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên Chủ đề</th>
            <th>Mô tả</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['vocabularySet_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['vocabulary_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td class="action-buttons">
                        <a href="view_vocabulary.php?vocabularySet_id=<?php echo $row['vocabularySet_id']; ?>" class="view-button">Xem từ vựng</a>
                        <a href="edit_topic.php?vocabularySet_id=<?php echo $row['vocabularySet_id']; ?>" class="edit-button">Sửa</a>
                        <a href="#" class="delete-button" onclick="confirmDelete(<?php echo $row['vocabularySet_id']; ?>)">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Không có chủ đề nào.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Modal xác nhận -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 style="color: red;">Chủ đề này vẫn còn từ vựng bên trong, bạn có chắc chắn muốn xóa chủ đề này?</h3>
        <div class="modal-button-container">
            <button onclick="deleteTopic()" style="background-color: red; color: white;">Xóa</button>
            <button onclick="closeModal()">Hủy</button>
        </div>
    </div>
</div>

<!-- Phân trang -->
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="list_vocabulary.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>" 
           class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>

<script>
    let topicIdToDelete = null;

    function confirmDelete(id) {
        topicIdToDelete = id;
        document.getElementById("deleteModal").style.display = "block";
    }

    function closeModal() {
        document.getElementById("deleteModal").style.display = "none";
    }

    function deleteTopic() {
        if (topicIdToDelete !== null) {
            window.location.href = "delete_topic.php?vocabularySet_id=" + topicIdToDelete;
        }
    }

    // Ẩn thông báo sau 3 giây
    document.addEventListener("DOMContentLoaded", function () {
        const alertMessage = document.getElementById("alert-message");
        if (alertMessage) {
            setTimeout(() => {
                alertMessage.style.display = "none";
            }, 3000); // 3000ms = 3 giây
        }
    });
</script>

</body>
</html>
