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

// Lấy danh sách các chủ đề từ bảng vocabulary_set
$query = "SELECT vocabularySet_id, vocabulary_name FROM vocabulary_set";
$result = $conn->query($query);
$topics = [];
while ($row = $result->fetch_assoc()) {
    $topics[] = $row;
}

// Xử lý thêm chủ đề
// Xử lý thêm chủ đề
if (isset($_GET['action']) && $_GET['action'] === 'add_topic' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $vocabulary_name = $conn->real_escape_string($_POST['vocabulary_name']);

    // Kiểm tra nếu tên chủ đề đã tồn tại
    $check_query = "SELECT * FROM vocabulary_set WHERE vocabulary_name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $vocabulary_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Nếu đã tồn tại, thông báo lỗi
        $message = "Chủ đề này đã tồn tại. Vui lòng chọn tên khác.";
    } else {
        // Nếu chưa tồn tại, thực hiện thêm chủ đề mới
        $insert_query = "INSERT INTO vocabulary_set (vocabulary_name) VALUES (?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("s", $vocabulary_name);
        if ($stmt->execute()) {
            $message = "Chủ đề mới đã được thêm thành công!";
        } else {
            $message = "Có lỗi xảy ra khi thêm chủ đề: " . $conn->error;
        }
    }
    $stmt->close();
}


// Xử lý xóa chủ đề
if (isset($_GET['action']) && $_GET['action'] === 'delete_topic' && isset($_GET['topic_id'])) {
    $topic_id = intval($_GET['topic_id']);

    // Xóa các từ vựng liên quan đến chủ đề này trước
    $delete_flashcards_query = "DELETE FROM flashcard WHERE vocabularySet_id = ?";
    $stmt = $conn->prepare($delete_flashcards_query);
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $stmt->close();

    // Xóa chủ đề
    $delete_topic_query = "DELETE FROM vocabulary_set WHERE vocabularySet_id = ?";
    $stmt = $conn->prepare($delete_topic_query);
    $stmt->bind_param("i", $topic_id);
    if ($stmt->execute()) {
        $message = "Chủ đề đã được xóa thành công!";
    } else {
        $message = "Có lỗi xảy ra: " . $conn->error;
    }
    $stmt->close();
}


// Xử lý thêm từ vựng vào chủ đề
if (isset($_GET['action']) && $_GET['action'] === 'add_vocab' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = intval($_POST['vocabularySet_id']);
    $vocab = $conn->real_escape_string($_POST['vocab']);
    $ipa = $conn->real_escape_string($_POST['ipa']);
    $meaning = $conn->real_escape_string($_POST['meaning']);
    $insert_query = "INSERT INTO flashcard (vocabularySet_id, vocab, ipa, meaning) VALUES ('$topic_id', '$vocab', '$ipa', '$meaning')";
    if ($conn->query($insert_query)) {
        $message = "Từ vựng mới đã được thêm thành công!";
    } else {
        $message = "Có lỗi xảy ra: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vocab_id']) && $_GET['action'] === 'delete_vocab') {
    $flashcard_id = intval($_POST['vocab_id']);
    $topic_id = intval($_GET['topic_id']);
    $delete_query = "DELETE FROM flashcard WHERE flashcard_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $flashcard_id);
    if ($stmt->execute()) {
        $message = "Từ vựng đã được xóa thành công!";
    } else {
        $message = "Có lỗi xảy ra khi xóa từ vựng: " . $conn->error;
    }
    $stmt->close();

    // Chuyển hướng sau khi xóa
    header("Location: list_vocabulary.php?action=view_vocab&topic_id=$topic_id");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bộ Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css_admin/list_vocabulary.css">
</head>
<body>
    <!-- Header -->
    <div class="container-fluid p-3 bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Quản Lý Bộ Từ Vựng</h1>
            <a href="admin_dashboard.php" class="btn btn-light">Quay Lại</a>
        </div>
    </div>

    <!-- Hiển thị thông báo -->
    <div class="container mt-4">
        <?php if (!empty($message)): ?>
            <div class="alert <?= $conn->error ? 'alert-danger' : 'alert-success'; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Chọn chức năng -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Chủ Đề</h5>
                        <a href="?action=view_topics" class="btn btn-primary">Xem Chủ Đề</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Quản Lý Chủ Đề</h5>
                        <a href="?action=add_topic" class="btn btn-success">Thêm Chủ Đề</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Quản Lý Bộ Từ Vựng</h5>
                        <a href="?action=manage_topics" class="btn btn-secondary">Thêm Từ Vựng</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nội dung theo action -->
    <div class="container mt-5">
        <?php if (isset($_GET['action']) && $_GET['action'] === 'view_topics'): ?>
            <!-- Hiển thị danh sách chủ đề -->
            <h3 class="mb-4">Danh Sách Chủ Đề</h3>
            <ul class="list-group">
                <?php foreach ($topics as $topic): ?>
                    <li class="list-group-item">
                        <a href="?action=view_vocab&topic_id=<?= $topic['vocabularySet_id']; ?>">
                            <?= htmlspecialchars($topic['vocabulary_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'view_vocab' && isset($_GET['topic_id'])): ?>
            <!-- Hiển thị từ vựng của một chủ đề -->
            <?php
            $topic_id = intval($_GET['topic_id']);
            $query = "SELECT flashcard_id, vocab, ipa, meaning FROM flashcard WHERE vocabularySet_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $topic_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $vocab_list = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            ?>
            <h3 class="mb-4">Từ Vựng Trong Chủ Đề</h3>
            <table class="table table-bordered">
            <thead>
            <tr>
                <th>Từ Vựng</th>
                <th>IPA</th>
                <th>Nghĩa</th>
                <th>Hành Động</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($vocab_list as $vocab): ?>
                <tr>
                    <td><?= htmlspecialchars($vocab['vocab']); ?></td>
                    <td><?= htmlspecialchars($vocab['ipa']); ?></td>
                    <td><?= htmlspecialchars($vocab['meaning']); ?></td>
                    <td>
                        <form method="POST" action="?action=delete_vocab&topic_id=<?= $topic_id; ?>" 
                         onsubmit="return confirm('Bạn có chắc chắn muốn xóa từ vựng này?');">
                        <input type="hidden" name="vocab_id" value="<?= $vocab['flashcard_id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

            <a href="list_vocabulary.php?action=view_topics" class="btn btn-secondary">Quay Lại</a>

        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'add_topic'): ?>
            <!-- Form thêm chủ đề -->
            
            <h3 class="mb-4">Danh Sách Chủ Đề</h3>
            <table class="table table-bordered">
        <thead>
        <tr>
            <th>Tên Chủ Đề</th>
            <th>Hành Động</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($topics as $topic): ?>
                <tr>
                    <td>
                        <a href="?action=view_vocab&topic_id=<?= $topic['vocabularySet_id']; ?>">
                            <?= htmlspecialchars($topic['vocabulary_name']); ?>
                        </a>
                    </td>
                    <td>
                        <form action="?action=delete_topic&topic_id=<?= $topic['vocabularySet_id']; ?>" method="POST" style="display:inline;">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa chủ đề này?');">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>

            <h3 class="mb-4">Thêm Chủ Đề Mới</h3>
            <form method="POST">
                <div class="mb-3">
                    <label for="vocabulary_name" class="form-label">Tên Chủ Đề</label>
                    <input type="text" id="vocabulary_name" name="vocabulary_name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Thêm Chủ Đề</button>
                <a href="list_vocabulary.php" class="btn btn-secondary">Quay Lại</a>
            </form>

        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'manage_topics'): ?>
            <!-- Hiển thị quản lý chủ đề -->
            <h3 class="mb-4">Danh Sách Chủ Đề</h3>
            <ul class="list-group">
                <?php foreach ($topics as $topic): ?>
                    <li class="list-group-item">
                        <a href="?action=add_vocab&topic_id=<?= $topic['vocabularySet_id']; ?>">
                            <?= htmlspecialchars($topic['vocabulary_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php elseif (isset($_GET['action']) && $_GET['action'] === 'add_vocab' && isset($_GET['topic_id'])): ?>
            <!-- Form thêm từ vựng -->
            <?php $topic_id = intval($_GET['topic_id']); ?>
            <h3 class="mb-4">Thêm Từ Vựng Mới</h3>
            <form action="?action=add_vocab" method="POST">
                <input type="hidden" name="vocabularySet_id" value="<?= $topic_id; ?>">
                <div class="mb-3">
                    <label for="vocab" class="form-label">Từ Vựng</label>
                    <input type="text" id="vocab" name="vocab" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="ipa" class="form-label">IPA</label>
                    <input type="text" id="ipa" name="ipa" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="meaning" class="form-label">Nghĩa</label>
                    <input type="text" id="meaning" name="meaning" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Thêm Từ Vựng</button>
                <a href="list_vocabulary.php?action=manage_topics" class="btn btn-secondary">Quay Lại</a>
            </form>

        <?php endif; ?>
    </div>
</body>
</html>
