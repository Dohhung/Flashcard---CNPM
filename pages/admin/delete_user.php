<?php
session_start();
include('../../db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: manage_user.php');
exit();
?>
