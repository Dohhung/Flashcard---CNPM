<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID flashcard từ form
if (isset($_POST['flashcard_id'])) {
    $flashcard_id = (int)$_POST['flashcard_id'];

    // Kiểm tra xem flashcard có tồn tại
    $query = "SELECT * FROM flashcard WHERE flashcard_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $flashcard_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $flashcard = $result->fetch_assoc();
        $vocabularySet_id = $flashcard['vocabularySet_id'];
        
        // Xóa flashcard
        $delete_query = "DELETE FROM flashcard WHERE flashcard_id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param('i', $flashcard_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Xóa hình ảnh liên quan đến flashcard (nếu có)
        if (!empty($flashcard['image_path'])) {
            $image_path = '../images/' . $flashcard['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path); // Xóa tệp hình ảnh
            }
        }

        // Chuyển hướng về trang flashcards của bộ từ vựng
        header("Location: flashcards.php?vocabularySet_id=$vocabularySet_id");
        exit();
    } else {
        echo "Flashcard không tồn tại.";
    }
} else {
    echo "ID Flashcard không hợp lệ.";
}

$conn->close();
?>
