<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

$flashcard_id = $_GET['flashcard_id'] ?? 0;
$vocabularySet_id = $_GET['vocabularySet_id'] ?? 0;

// Xóa từ vựng
$query = "DELETE FROM flashcard WHERE flashcard_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $flashcard_id);
$stmt->execute();
$stmt->close();

// Chuyển hướng về trang view_vocabulary.php
header("Location: view_vocabulary.php?vocabularySet_id=$vocabularySet_id");
exit();
?>
