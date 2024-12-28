<?php
session_start();
include('../db.php');

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy tuần hiện tại hoặc tuần được chọn
$week = isset($_GET['week']) ? (int)$_GET['week'] : 0;

// Tính ngày bắt đầu và kết thúc của tuần
$currentDate = new DateTime();
$currentDate->modify(($week * 7) . ' days');
$currentWeekStart = clone $currentDate;
$currentWeekStart->modify('Monday this week');
$currentWeekEnd = clone $currentWeekStart;
$currentWeekEnd->modify('+6 days');

// Truy vấn dữ liệu hoạt động trong tuần
$query = "SELECT DATE(activity_date) AS activity_date, SUM(TIME_TO_SEC(duration)) / 60 AS total_duration
          FROM history
          WHERE user_id = ? AND DATE(activity_date) BETWEEN ? AND ?
          GROUP BY DATE(activity_date)";
$stmt = $conn->prepare($query);
$startDate = $currentWeekStart->format('Y-m-d');
$endDate = $currentWeekEnd->format('Y-m-d');
$stmt->bind_param('iss', $user_id, $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Chuẩn bị dữ liệu cho tuần
$weekData = array_fill(0, 7, 0); // Mặc định giá trị 0 cho 7 ngày (Thứ 2 -> Chủ nhật)
$daysOfWeek = ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ Nhật'];

while ($row = $result->fetch_assoc()) {
    $dayOfWeek = (int)date('N', strtotime($row['activity_date'])) - 1; // Chuyển ngày thành thứ trong tuần (0 -> Thứ 2)
    $weekData[$dayOfWeek] = round($row['total_duration'], 2); // Thời gian hoạt động (phút)
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Hoạt Động</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/history.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include('../templates/header.php'); ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Lịch Sử Hoạt Động</h1>

        <!-- Điều hướng tuần -->
        <div class="text-center mb-4">
            <a href="?week=<?php echo $week - 1; ?>" class="btn btn-primary">Tuần Trước</a>
            <span class="mx-3">
                Tuần: <?php echo $currentWeekStart->format('d/m/Y'); ?> - <?php echo $currentWeekEnd->format('d/m/Y'); ?>
            </span>
            <a href="?week=<?php echo $week + 1; ?>" class="btn btn-primary">Tuần Sau</a>
        </div>
        
        <!-- Biểu đồ cột -->
        <div class="chart-container" style="width: 80%; margin: auto;">
            <canvas id="activityChart" width="400" height="200"></canvas>
        </div>

        <script>
            // Dữ liệu từ PHP
            const weekData = <?php echo json_encode($weekData); ?>;
            const daysOfWeek = <?php echo json_encode($daysOfWeek); ?>;

            // Tạo danh sách ngày tháng năm cho tooltip
            const startDate = new Date('<?php echo $currentWeekStart->format('Y-m-d'); ?>');
            const dates = Array.from({ length: 7 }, (_, i) => {
                const date = new Date(startDate);
                date.setDate(date.getDate() + i);
                return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
            });

            // Tạo biểu đồ bằng Chart.js
            const ctx = document.getElementById('activityChart').getContext('2d');
            const activityChart = new Chart(ctx, {
                type: 'bar', // Loại biểu đồ
                data: {
                    labels: daysOfWeek, // Nhãn trục X: Thứ 2 -> Chủ Nhật
                    datasets: [{
                        label: 'Thời gian học tập (phút)', // Ghi chú
                        data: weekData, // Dữ liệu thời gian học
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', // Màu nền cột
                        borderColor: 'rgba(75, 192, 192, 1)',       // Màu viền cột
                        borderWidth: 1                              // Độ dày viền
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true, // Bắt đầu từ 0
                            title: {
                                display: true,
                                text: 'Thời gian (phút)',
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Ngày trong tuần',
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function (context) {
                                    const index = context[0].dataIndex;
                                    return dates[index]; // Hiển thị ngày tháng năm
                                },
                                label: function (context) {
                                    const value = context.raw;
                                    return `Thời gian học: ${value} phút`; // Hiển thị thời gian học tập
                                }
                            }
                        },
                        legend: {
                            display: false // Ẩn phần chú thích
                        }
                    }
                }
            });
        </script>


    </div>

    <?php include('../templates/footer.php'); ?>
</body>
</html>
