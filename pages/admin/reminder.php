<?php 
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu
require '../../vendor/autoload.php'; // Đường dẫn đến autoload của Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Xử lý cập nhật tin nhắn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $daily_message = $conn->real_escape_string($_POST['daily_message']);
    $hours_48_message = $conn->real_escape_string($_POST['hours_48_message']);

    $update_query = "
        UPDATE default_messages SET message_text = CASE 
            WHEN message_type = 'daily' THEN '$daily_message'
            WHEN message_type = '48_hours' THEN '$hours_48_message'
        END
        WHERE message_type IN ('daily', '48_hours');
    ";
    if ($conn->query($update_query)) {
        $message = "Tin nhắn mặc định đã được cập nhật!";
    } else {
        $message = "Cập nhật thất bại: " . $conn->error;
    }
}

// Lấy tin nhắn mặc định
$query = "SELECT message_type, message_text FROM default_messages";
$result = $conn->query($query);

$default_messages = [];
while ($row = $result->fetch_assoc()) {
    $default_messages[$row['message_type']] = $row['message_text'];
}

// Gửi nhắc nhở người dùng
$messages_sent = [];
$current_time = new DateTime();

// Lấy danh sách người dùng và hoạt động gần nhất
$user_query = "
    SELECT u.user_id, u.username, u.email, u.time_logout
    FROM user u
    WHERE u.role = 'user'
";
$user_result = $conn->query($user_query);

while ($row = $user_result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $username = $row['username'];
    $email = $row['email']; // Lấy email của người dùng
    $last_logout = $row['time_logout'] ? new DateTime($row['time_logout']) : null;
    //var_dump($row);
    $time_difference = $last_logout ? $current_time->getTimestamp() - $last_logout->getTimestamp() : null;
    if($time_difference === null){
        continue;
    }
    $hours_since_last_activity = $time_difference ? floor($time_difference / 3600) : null;

    // Kiểm tra trạng thái nhắc nhở cuối cùng
    $reminder_query = "
    SELECT MAX(reminder_date) AS last_reminder
    FROM reminder
    WHERE user_id = $user_id
    ";
    $reminder_result = $conn->query($reminder_query);
    $last_reminder_row = $reminder_result->fetch_assoc();
    $last_reminder = $last_reminder_row && $last_reminder_row['last_reminder']
        ? new DateTime($last_reminder_row['last_reminder'])
        : null;
    $hours_since_last_reminder = $last_reminder ? floor(($current_time->getTimestamp() - $last_reminder->getTimestamp()) / 3600) : null;
    echo"<br>";
    //var_dump($last_reminder);
    echo"<br>";
    //var_dump($last_logout);
    echo"<br>";
    //var_dump($hours_since_last_reminder);
    if (!$last_logout) {
        // Người dùng chưa bao giờ học
        if (!$last_reminder || $hours_since_last_reminder >=24) {
            $message = str_replace('{username}', $username, $default_messages['daily']);
            $messages_sent[] = sendReminder($conn, $user_id, $username, $message, $current_time, $email);
        }
    } elseif ($hours_since_last_activity > 48) {
        // Người dùng không hoạt động trong hơn 48 giờ
        if (!$last_reminder || $hours_since_last_reminder >= 24) {
            $message = str_replace('{username}', $username, $default_messages['48_hours']);
            $messages_sent[] = sendReminder($conn, $user_id, $username, $message, $current_time, $email);
        }
    } elseif ($hours_since_last_activity >= 24 && $hours_since_last_activity <= 48) {
        // Người dùng không hoạt động trong khoảng từ 24 đến 48 giờ
        if (!$last_reminder || $hours_since_last_reminder >= 24) {
            $message = str_replace('{username}', $username, $default_messages['daily']);
            $messages_sent[] = sendReminder($conn, $user_id, $username, $message, $current_time, $email);
        }
    }
    echo "<br><br><br><br>";
    //var_dump($messages_sent);
}

function sendReminder($conn, $user_id, $username, $message, $current_time, $email) {
    $reminder_query = "
        INSERT INTO reminder (user_id, reminder_date, status) 
        VALUES ($user_id, NOW(), 'Complete');
    ";
    $conn->query($reminder_query);

    // Gửi email
    sendEmailNotification($email, $username, $message);

    return [
        'username' => $username,
        'message' => $message,
        'date' => $current_time->format('Y-m-d H:i:s')
    ];
}

function sendEmailNotification($recipientEmail, $recipientName, $message) {
    $mail = new PHPMailer(true);
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Máy chủ SMTP của Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'tranthanhdatsr11@gmail.com'; // Email Gmail của bạn
        $mail->Password = 'datvattuong963'; // Mật khẩu ứng dụng Gmail (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Người gửi
        $mail->setFrom('tranthanhsr11@gmail.com', 'Trần Đạt'); // Địa chỉ email và tên người gửi

        // Người nhận
        $mail->addAddress($recipientEmail, $recipientName);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Nhắc nhở từ E-Learn App';
        $mail->Body = "<p>Chào $recipientName,</p><p>$message</p><p>Thân ái,<br>E-Learn App</p>";

        // Gửi email
        $mail->send();
    } catch (Exception $e) {
        echo "Không thể gửi email. Lỗi: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhắc Nhở</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Quản Lý Tin Nhắn Nhắc Nhở</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Form chỉnh sửa tin nhắn mặc định -->
        <form method="POST" action="reminder.php">
            <div class="mb-3">
                <label for="daily_message" class="form-label">Tin nhắn nhắc nhở hàng ngày:</label>
                <textarea id="daily_message" name="daily_message" class="form-control" rows="3"><?= htmlspecialchars($default_messages['daily'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label for="hours_48_message" class="form-label">Tin nhắn nhắc nhở sau 48 giờ:</label>
                <textarea id="hours_48_message" name="hours_48_message" class="form-control" rows="3"><?= htmlspecialchars($default_messages['48_hours'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Cập Nhật Tin Nhắn</button>
        </form>

        <!-- Danh sách tin nhắn đã gửi -->
        <h2 class="mt-5">Danh Sách Tin Nhắn Đã Gửi</h2>
        <?php
        //var_dump($messages_sent);
         if (!empty($messages_sent)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên Người Dùng</th>
                        <th>Nội Dung Tin Nhắn</th>
                        <th>Thời Gian Gửi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages_sent as $sent):?>
                        <tr>
                            <td><?= htmlspecialchars($sent['username']) ?></td>
                            <td><?= htmlspecialchars($sent['message']) ?></td>
                            <td><?= htmlspecialchars($sent['date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Không có tin nhắn nào được gửi.</p>
        <?php endif; ?>

        <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Quay lại</a>
    </div>
</body>
</html>
