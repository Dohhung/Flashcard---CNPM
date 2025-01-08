<?php
session_start();
include('../../db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra nếu chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

// Lấy user_id từ query string
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    header("Location: progress_chart.php"); // Quay lại trang danh sách nếu không có user_id
    exit();
}

$user_id = $_GET['user_id'];

// Lấy thông tin người dùng
$userQuery = "SELECT username, email FROM user WHERE user_id = ? AND role = 'user'";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();
if (!$user) {
    header("Location: progress_chart.php"); // Quay lại nếu user không tồn tại
    exit();
}
$stmt->close();

// Lấy dữ liệu lịch sử học tập theo tuần
$query = "SELECT DATE(activity_date) AS activity_date, SUM(TIME_TO_SEC(duration)) / 60 AS total_duration
          FROM history
          WHERE user_id = ?
          GROUP BY DATE(activity_date)
          ORDER BY activity_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Chuẩn bị dữ liệu cho biểu đồ
$chartData = [];
while ($row = $result->fetch_assoc()) {
    $chartData[] = [
        'date' => $row['activity_date'],
        'duration' => round($row['total_duration'], 2)
    ];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Tiến Trình Học Tập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Tiến Trình Học Tập Của <?= htmlspecialchars($user['username']) ?></h1>

        <div class="mb-4">
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <!-- Biểu đồ -->
        <div class="chart-container" style="width: 80%; margin: auto;">
            <canvas id="progressChart" width="400" height="200"></canvas>
        </div>

        <script>
            // Dữ liệu từ PHP
            const chartData = <?php echo json_encode($chartData); ?>;
            const labels = chartData.map(data => data.date);
            const durations = chartData.map(data => data.duration);

            // Tạo biểu đồ bằng Chart.js
            const ctx = document.getElementById('progressChart').getContext('2d');
            const progressChart = new Chart(ctx, {
                type: 'line', // Biểu đồ dáng đường
                data: {
                    labels: labels, // Ngày học tập
                    datasets: [{
                        label: 'Thời gian học tập (phút)',
                        data: durations, // Thời gian học tập
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        tension: 0.2 // Đồ cong của đường
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Thời gian (phút)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Ngày'
                            }
                        }
                    }
                }
            });
        </script>

        <!-- Nút quay lại -->
        <div class="text-center mt-4">
            <a href="/FC/pages/admin/progress.php" class="btn btn-secondary">Quay Lại</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
