<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    header('Location: flashcards.php');
    exit();
}


// Kiểm tra nếu có hình ảnh được tải lên
$imagePath = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = '../images/'; // Thư mục nơi bạn muốn lưu hình ảnh
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
    $vocab = trim($_POST['vocab']);
    $ipa = $_POST['ipa'];
    $meaning = $_POST['meaning'];
    $flashcard_type = $_POST['flashcard_type'];
    $example = $_POST['example'];

    // Ràng buộc toàn vẹn: Kiểm tra xem các trường bắt buộc có được nhập đầy đủ không
    if (empty($vocab) || empty($ipa) || empty($meaning)) {
        $error_message = "Vui lòng điền đầy đủ các trường: Từ vựng, IPA và Nghĩa.";
    } else {
        // Kiểm tra xem từ vựng đã tồn tại trong bộ từ vựng hay chưa
        $query = "SELECT * FROM flashcard WHERE vocabularySet_id = ? AND vocab = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $vocabularySet_id, $vocab);
        $stmt->execute();
        $result = $stmt->get_result();

        // Nếu từ vựng đã tồn tại trong cơ sở dữ liệu
        if ($result->num_rows > 0) {
            $error_message = "Từ vựng này đã tồn tại trong bộ từ vựng.";
        } else {
            // Nếu từ vựng chưa tồn tại, thêm vào cơ sở dữ liệu
            $query = "INSERT INTO flashcard (vocabularySet_id, vocab, ipa, meaning, flashcard_type, example, image_path) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssss", $vocabularySet_id, $vocab, $ipa, $meaning, $flashcard_type, $example, $imagePath);

            if ($stmt->execute()) {
                // Nếu thêm thành công, hiển thị thông báo thành công
                $success_message = "Đã thêm từ vựng mới thành công!";
            } else {
                $error_message = "Có lỗi xảy ra khi thêm từ vựng. Vui lòng thử lại.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Từ Mới</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
    <?php include('../templates/header.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Thêm Từ Mới</h1>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert" id="alertMessage">
                <?= htmlspecialchars($success_message); ?>
            </div>
            <script>hideAlert();</script>
            <script>
                // Tự động làm mới trang sau 2 giây
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert" id="alertMessage">
                <?= htmlspecialchars($error_message); ?>
            </div>
            <script>hideAlert();</script>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" >
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="vocab" class="form-label">Từ vựng</label>
                    <input type="text" class="form-control" id="vocab" name="vocab" required>
                </div>
                <div class="col-md-6">
                    <label for="ipa" class="form-label">IPA</label>
                    <input type="text" class="form-control" id="ipa" name="ipa" required>
                </div>
                <div class="col-md-6">
                    <label for="meaning" class="form-label">Nghĩa</label>
                    <input type="text" class="form-control" id="meaning" name="meaning" required>
                </div>
                <div class="col-md-6">
                    <label for="flashcard_type" class="form-label">Loại Từ</label>
                    <input type="text" class="form-control" id="flashcard_type" name="flashcard_type" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="example" class="form-label">Ví Dụ</label>
                <textarea class="form-control" id="example" name="example" required></textarea>
            </div>
            <div class="col-md-6">
                <label for="image" class="form-label">Hình ảnh (tuỳ chọn)</label>
                <input type="file" class="form-control" id="image" name="image">
            </div>
            <br>
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">Thêm Từ</button>
                <a href="flashcards.php?vocabularySet_id=<?php echo $vocabularySet_id; ?>" class="btn btn-secondary">Quay Lại</a>
            </div>
        </form>
    </div>

    <?php include('../templates/footer.php'); ?>

    <!-- JavaScript để tự động ẩn thông báo sau 3 giây -->
    <script>
        setTimeout(function () {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                alert.classList.add('hide');
                setTimeout(() => alert.remove(), 500); // Loại bỏ khỏi DOM sau khi ẩn
            }
        }, 3000);
    </script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
