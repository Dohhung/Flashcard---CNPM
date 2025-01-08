<?php
session_start();
include('../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Lấy thời gian đăng nhập gần nhất từ bảng history
    $query = "
        SELECT History_id, activity_date 
        FROM history 
        WHERE user_id = ? AND duration IS NULL 
        ORDER BY activity_date DESC 
        LIMIT 1;
    ";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $history_id = $row['History_id'];
            $login_time = $row['activity_date'];

            // Tính khoảng thời gian học tập (duration)
            if ($login_time) {
                try {
                    // Đảm bảo timezone đồng bộ
                    $timezone = new DateTimeZone('Asia/Ho_Chi_Minh'); // Chỉnh timezone nếu cần
                    $logout_time = new DateTime('now', $timezone); // Thời gian đăng xuất
                    $login_time = new DateTime($login_time, $timezone); // Thời gian đăng nhập

                    // Tính khoảng cách thời gian
                    $interval = $login_time->diff($logout_time); // Tính khoảng cách thời gian
                    $duration = $interval->format('%H:%I:%S'); // Định dạng thời gian thành "HH:MM:SS"
                } catch (Exception $e) {
                    echo "Lỗi khi tính toán khoảng thời gian: " . $e->getMessage();
                    exit();
                }
            } else {
                exit();
            }

            // Cập nhật thời gian học tập vào bảng history
            $update_query = "UPDATE history SET duration = ? WHERE History_id = ?";
            $update_stmt = $conn->prepare($update_query);

            if ($update_stmt) {
                $update_stmt->bind_param('si', $duration, $history_id);
                $update_stmt->execute();
                $update_stmt->close();
            }

            // Cập nhật login_time và logout_time vào bảng user
            $update_user_query = "UPDATE user SET time_login = ?, time_logout = ? WHERE user_id = ?";
            $update_user_stmt = $conn->prepare($update_user_query);

            if ($update_user_stmt) {
                $formatted_login_time = $login_time->format('Y-m-d H:i:s');
                $formatted_logout_time = $logout_time->format('Y-m-d H:i:s');
                $update_user_stmt->bind_param('ssi', $formatted_login_time, $formatted_logout_time, $user_id);
                $update_user_stmt->execute();
                $update_user_stmt->close();
            }
        }
        $stmt->close();
    }
}

// Xóa session và chuyển hướng về trang đăng nhập
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
