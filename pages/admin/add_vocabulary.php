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

// Kiểm tra nếu có hình ảnh được tải lên
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = '../../images/'; // Thư mục nơi bạn muốn lưu hình ảnh
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageName = basename($_FILES['image']['name']);
    $targetFile = $targetDir . $imageName;

    // Kiểm tra nếu tệp là hình ảnh
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowedTypes)) {
        // Di chuyển tệp đến thư mục đích
        if (move_uploaded_file($imageTmpName, $targetFile)) {
            $imagePath = $imageName; // Đường dẫn lưu trong cơ sở dữ liệu
        } else {
            echo "Có lỗi khi tải lên hình ảnh.";
        }
    } else {
        echo "Chỉ chấp nhận tệp hình ảnh (jpg, jpeg, png, gif).";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $vocab = $_POST['vocab'];
    $ipa = $_POST['ipa'];
    $meaning = $_POST['meaning'];
    $flashcard_type = $_POST['flashcard_type'];
    $example = $_POST['example'];

    // Ràng buộc toàn vẹn: Kiểm tra xem các trường bắt buộc có được nhập đầy đủ không
    if (empty($vocab) || empty($ipa) || empty($meaning)) {
        $error_message = "Vui lòng điền đầy đủ các trường: Từ vựng, IPA và Nghĩa.";
    } else {
        // Kiểm tra xem từ vựng đã tồn tại trong cơ sở dữ liệu hay chưa
        $query = "SELECT * FROM flashcard WHERE vocabularySet_id = ? AND vocab = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $vocabularySet_id, $vocab);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Nếu tìm thấy từ vựng trùng, kiểm tra IPA
        if ($result->num_rows > 0) {
            $existingFlashcard = $result->fetch_assoc();
            if ($existingFlashcard['ipa'] === $ipa) {
                // Nếu IPA giống nhau, thông báo lỗi
                $error_message = "Từ vựng này đã có trong cơ sở dữ liệu với IPA tương ứng.";
            } else {
                // Nếu IPA khác nhau, cho phép thêm
                $error_message = "Từ vựng này đã tồn tại nhưng IPA khác nhau, sẽ thêm từ vựng mới.";
            }
        } else {
            // Nếu không có từ vựng trùng, thêm vào cơ sở dữ liệu
            $query = "INSERT INTO flashcard (vocabularySet_id, vocab, ipa, meaning, flashcard_type, example, image_path) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssss", $vocabularySet_id, $vocab, $ipa, $meaning, $flashcard_type, $example, $imagePath);
            $stmt->execute();
            $stmt->close();

            // Chuyển hướng về trang view_vocabulary.php
            header("Location: view_vocabulary.php?vocabularySet_id=$vocabularySet_id");
            exit();
        }

        $stmt->close();
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
    <link rel="stylesheet" href="../css_admin/style.css">
    <script>
        // Hàm ẩn thông báo sau 3 giây
        function hideAlert() {
            setTimeout(function() {
                var alertElement = document.getElementById('alertMessage');
                if (alertElement) {
                    alertElement.style.display = 'none';
                }
            }, 3000); // 3000 milliseconds = 3 seconds
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <!-- Hiển thị tên chủ đề trong tiêu đề -->
        <h1 class="text-center mb-4"><?= htmlspecialchars($set['vocabulary_name']); ?></h1>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert" id="alertMessage">
                <?= htmlspecialchars($error_message); ?>
            </div>
            <script>hideAlert();</script> <!-- Gọi hàm ẩn thông báo -->
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
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
                <label for="image" class="form-label">Hình Ảnh</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Lưu Từ Vựng</button>
        </form>
        <br>
        <a href="view_vocabulary.php?vocabularySet_id=<?= $vocabularySet_id ?>" class="btn btn-light">Quay Lại</a>
    </div>
    
</body>
</html>
