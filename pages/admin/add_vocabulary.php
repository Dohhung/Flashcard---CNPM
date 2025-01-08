<?php 
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

// Truy vấn để lấy thông tin chủ đề
$query = "SELECT vocabulary_name FROM vocabulary_set WHERE vocabularySet_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vocabularySet_id);
$stmt->execute();
$set = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Nếu không tìm thấy chủ đề, chuyển hướng về trang danh sách chủ đề
if (!$set) {
    header('Location: list_vocabulary.php');
    exit();
}
require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['single_submit'])) {
        // Xử lý thêm từ đơn lẻ
        $vocab = $_POST['vocab'];
        $ipa = $_POST['ipa'];
        $meaning = $_POST['meaning'];
        $flashcard_type = $_POST['flashcard_type'];
        $example = $_POST['example'];
        $imagePath = $_POST['image_url'];

        // Ràng buộc toàn vẹn: Kiểm tra xem các trường bắt buộc có được nhập đầy đủ không
        if (empty($vocab) || empty($ipa) || empty($meaning) || empty($imagePath)) {
            $error_message = "Vui lòng điền đầy đủ các trường.";
        } else {
            $query = "INSERT INTO flashcard (vocabularySet_id, vocab, ipa, meaning, flashcard_type, example, image_path) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssss", $vocabularySet_id, $vocab, $ipa, $meaning, $flashcard_type, $example, $imagePath);
            $stmt->execute();
            $stmt->close();

            $success_message = "Thêm từ vựng thành công!";
        }
    } elseif (isset($_POST['bulk_submit'])) {
        // Xử lý thêm từ hàng loạt từ file Excel
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpName = $_FILES['excel_file']['tmp_name'];
            $spreadsheet = IOFactory::load($fileTmpName);
            $data = $spreadsheet->getActiveSheet()->toArray();
            // Bỏ qua dòng tiêu đề và duyệt từng dòng
            foreach ($data as $index => $row) {
                if ($index === 0) continue; // Bỏ qua dòng đầu tiên

                list($vocab, $image_path, $ipa, $meaning, $flashcard_type, $example) = $row;

                // Kiểm tra ràng buộc toàn vẹn và thêm từ vào cơ sở dữ liệu
                if (!empty($vocab) && !empty($ipa) && !empty($meaning)) {
                    $query = "INSERT INTO flashcard (vocabularySet_id, image_path, vocab, ipa, meaning, flashcard_type, example) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("issssss", $vocabularySet_id, $image_path, $vocab, $ipa, $meaning, $flashcard_type, $example);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $success_message = "Thêm từ vựng hàng loạt thành công!";
        } else {
            $error_message = "Có lỗi khi tải lên file Excel.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Từ Vựng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><?= htmlspecialchars($set['vocabulary_name']); ?></h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($success_message); ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Form thêm từ đơn lẻ -->
        <form action="" method="POST">
            <h3>Thêm Từ Vựng Đơn Lẻ</h3>
            <div class="mb-3">
                <label for="vocab" class="form-label">Tên Từ Vựng</label>
                <input type="text" class="form-control" id="vocab" name="vocab" required>
            </div>
            <div class="mb-3">
                <label for="ipa" class="form-label">IPA</label>
                <input type="text" class="form-control" id="ipa" name="ipa" required>
            </div>
            <div class="mb-3">
                <label for="meaning" class="form-label">Nghĩa</label>
                <textarea class="form-control" id="meaning" name="meaning" required></textarea>
            </div>
            <div class="mb-3">
                <label for="flashcard_type" class="form-label">Loại Từ</label>
                <input type="text" class="form-control" id="flashcard_type" name="flashcard_type">
            </div>
            <div class="mb-3">
                <label for="example" class="form-label">Ví Dụ</label>
                <textarea class="form-control" id="example" name="example"></textarea>
            </div>
            <div class="mb-3">
                <label for="image_url" class="form-label">URL Hình Ảnh</label>
                <input type="url" class="form-control" id="image_url" name="image_url" required>
            </div>
            <button type="submit" name="single_submit" class="btn btn-primary">Lưu Từ Vựng</button>
        </form>

        <hr>

        <!-- Form thêm từ hàng loạt -->
        <form action="" method="POST" enctype="multipart/form-data">
            <h3>Thêm Từ Vựng Hàng Loạt (CSV)</h3>
            <div class="mb-3">
                <label for="excel_file" class="form-label">Tải Lên File CSV</label>
                <input type="file" class="form-control" id="excel_file" name="excel_file" required>
            </div>
            <button type="submit" name="bulk_submit" class="btn btn-secondary">Tải Lên</button>
        </form>

        <br>
        <a href="view_vocabulary.php?vocabularySet_id=<?= $vocabularySet_id ?>" class="btn btn-light">Quay Lại</a>
    </div>
</body>
</html>
