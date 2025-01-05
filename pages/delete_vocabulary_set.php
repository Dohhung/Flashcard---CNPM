<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy ID bộ từ vựng từ POST
$vocabularySet_id = isset($_POST['vocabularySet_id']) ? (int)$_POST['vocabularySet_id'] : 0;

// Kiểm tra xem bộ từ vựng có từ vựng hay không
$query_check_flashcards = "SELECT COUNT(*) as total FROM flashcard WHERE vocabularySet_id = ?";
$stmt_check = $conn->prepare($query_check_flashcards);
$stmt_check->bind_param('i', $vocabularySet_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$check_data = $result_check->fetch_assoc();
$has_flashcards = $check_data['total'] > 0;
$stmt_check->close();

// Nếu có từ vựng, không xóa mà yêu cầu xác nhận
if ($has_flashcards) {
    echo "<script>
            if (confirm('Bộ từ vựng này còn từ vựng, bạn vẫn muốn xóa?')) {
                location.href = 'delete_vocabulary_set.php?vocabularySet_id=$vocabularySet_id';
            }
          </script>";
    exit();
}

// Nếu không có từ vựng, xóa bộ từ vựng
$query_delete = "DELETE FROM vocabulary_set WHERE vocabularySet_id = ? AND user_id = ?";
$stmt_delete = $conn->prepare($query_delete);
$stmt_delete->bind_param('ii', $vocabularySet_id, $_SESSION['user_id']);
$stmt_delete->execute();
$stmt_delete->close();

// Chuyển hướng về trang bộ từ vựng
header("Location: vocabulary_sets.php");
exit();
