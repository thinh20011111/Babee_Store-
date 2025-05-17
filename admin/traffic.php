<?php
// Traffic statistics page for admin
if (!defined('ADMIN_INCLUDED')) {
    define('ADMIN_INCLUDED', true);
}

// Define debug mode
define('DEBUG_MODE', true); // Set to false in production

require_once '../models/TrafficLog.php';

// Include database connection
require_once '../config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Initialize debug log
$debug_logs = [];

// Initialize traffic log
$traffic = new TrafficLog($conn);

// Get statistics
try {
    // Lấy tổng số lượt truy cập
    $total_visits = $traffic->getTotalVisits() ?? 0;
    
    // Lấy số lượt truy cập hôm nay
    $today_visits = $traffic->getTodayVisits() ?? 0;
    
    // Lấy dữ liệu truy cập theo ngày (30 ngày gần nhất)
    $view_mode = isset($_GET['view']) ? $_GET['view'] : 'daily';
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    if ($view_mode == 'monthly') {
        $traffic_stats = $traffic->getStatsRange($start_date, $end_date, 'month');
        $debug_logs[] = "Fetched monthly traffic stats from $start_date to $end_date: " . count($traffic_stats) . " data points";
    } else {
        $traffic_stats = $traffic->getStatsRange($start_date, $end_date, 'day');
        $debug_logs[] = "Fetched daily traffic stats from $start_date to $end_date: " . count($traffic_stats) . " data points";
    }
    
    $debug_logs[] = "Traffic statistics fetched: Total=$total_visits, Today=$today_visits";
} catch (Exception $e) {
    $debug_logs[] = "Error fetching traffic statistics: " . $e->getMessage();
    error_log("Traffic statistics error: " . $e->getMessage());
    $traffic_stats = [];
}

// Nếu không có dữ liệu thực (bảng mới tạo), dùng dữ liệu mẫu để hiển thị demo
if (empty($traffic_stats)) {
    require_once '../models/sample/traffic_data.php';
    
    if ($view_mode == 'monthly') {
        $traffic_stats = getSampleMonthlyTraffic();
        $debug_logs[] = "Using sample monthly data: " . count($traffic_stats) . " data points";
    } else {
        $traffic_stats = getSampleDailyTraffic();
        $debug_logs[] = "Using sample daily data: " . count($traffic_stats) . " data points";
    }
    
    $total_visits = array_sum(array_column($traffic_stats, 'count'));
    $today_visits = end($traffic_stats)['count'] ?? 0;
}

// Prepare chart data
$chart_labels = [];
$chart_data = [];

foreach ($traffic_stats as $stat) {
    if ($view_mode == 'monthly') {
        $chart_labels[] = date('M Y', strtotime($stat['period'] . '-01'));
    } else {
        $chart_labels[] = date('d/m', strtotime($stat['period']));
    }
    $chart_data[] = $stat['count'];
}

// Convert to JSON for JavaScript
$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);

// Define currency constant
if (!defined('CURRENCY')) {
    define('CURRENCY', 'đ');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Statistics - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgo=">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 400px;
            background-color: #f8f9fa;
            border-radius: 0.375rem;
        }
        .chart-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #6c757d;
        }
        .debug-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9em;
            display: <?php echo DEBUG_MODE ? 'block' : 'none'; ?>;
        }
        .chart-controls {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .visit-card {
            border-left: 5px solid #0d6efd;
        }
        .view-toggle .btn {
            min-width: 100px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark sidebar p-3 text-white" style="width: 250px;">
            <h4 class="text-center mb-4">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'dashboard') ? 'active bg-primary' : ''; ?>" href="index.php?page=dashboard"><i class="fas fa-home me-2"></i> Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'orders') ? 'active bg-primary' : ''; ?>" href="index.php?page=orders"><i class="fas fa-shopping-cart me-2"></i> Đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'products') ? 'active bg-primary' : ''; ?>" href="index.php?page=products"><i class="fas fa-box me-2"></i> Sản phẩm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'users') ? 'active bg-primary' : ''; ?>" href="index.php?page=users"><i class="fas fa-users me-2"></i> Người dùng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'traffic') ? 'active bg-primary' : ''; ?>" href="index.php?page=traffic"><i class="fas fa-chart-line me-2"></i> Lượt truy cập</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'banners') ? 'active bg-primary' : ''; ?>" href="index.php?page=banners"><i class="fas fa-images me-2"></i> Giao diện</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'settings') ? 'active bg-primary' : ''; ?>" href="index.php?page=settings"><i class="fas fa-cog me-2"></i> Cài đặt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($_GET['page'] === 'promotions') ? 'active bg-primary' : ''; ?>" href="index.php?page=promotions"><i class="fas fa-tags me-2"></i> Khuyến mãi</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Thống kê lượt truy cập</h1>
                    <div class="d-flex">
                        <button class="btn btn-outline-primary me-2" id="downloadPDFBtn">
                            <i class="fas fa-file-pdf me-2"></i> Xuất PDF
                        </button>
                        <button class="btn btn-primary" id="refreshBtn">
                            <i class="fas fa-sync-alt me-2"></i> Làm mới
                        </button>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="chart-controls mb-4">
                    <form id="trafficFilterForm" class="row g-3 align-items-end" method="GET" action="">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Từ ngày</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                value="<?php echo htmlspecialchars($start_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Đến ngày</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                value="<?php echo htmlspecialchars($end_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Chế độ xem</label>
                            <div class="btn-group view-toggle" role="group">
                                <button type="button" class="btn btn-outline-primary <?php echo $view_mode == 'daily' ? 'active' : ''; ?>" 
                                    onclick="this.form.view.value='daily'; this.form.submit();">
                                    Theo ngày
                                </button>
                                <button type="button" class="btn btn-outline-primary <?php echo $view_mode == 'monthly' ? 'active' : ''; ?>"
                                    onclick="this.form.view.value='monthly'; this.form.submit();">
                                    Theo tháng
                                </button>
                            </div>
                            <input type="hidden" name="view" value="<?php echo htmlspecialchars($view_mode); ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Lọc dữ liệu
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 rounded visit-card">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-chart-line fa-3x text-primary me-3"></i>
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Tổng lượt truy cập</h6>
                                    <h4 class="mb-0"><?php echo number_format($total_visits); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 rounded visit-card">
                            <div class="card-body d-flex align-items-center">
                                <i class="fas fa-calendar-day fa-3x text-success me-3"></i>
                                <div>
                                    <h6 class="text-uppercase text-muted mb-1">Lượt truy cập hôm nay</h6>
                                    <h4 class="mb-0"><?php echo number_format($today_visits); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Traffic Chart -->
                <div class="card shadow-sm rounded mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i> 
                            <?php echo $view_mode == 'monthly' ? 'Lượt truy cập theo tháng' : 'Lượt truy cập theo ngày'; ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="trafficChart"></canvas>
                            <?php if (empty($chart_data)): ?>
                            <div class="chart-placeholder">
                                <div class="text-center">
                                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                    <p>Không có dữ liệu lượt truy cập trong khoảng thời gian đã chọn.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script>
        // Initialize Chart
        let trafficChart;

        const createTrafficChart = () => {
            const ctx = document.getElementById('trafficChart').getContext('2d');
            
            // Chart data
            const chartLabels = <?php echo $chart_labels_json; ?>;
            const chartData = <?php echo $chart_data_json; ?>;
            
            // Destroy existing chart if it exists
            if (trafficChart) {
                trafficChart.destroy();
            }

            // Chart colors
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(78, 115, 223, 0.8)');
            gradient.addColorStop(1, 'rgba(78, 115, 223, 0.1)');
            
            trafficChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: '<?php echo $view_mode == "monthly" ? "Lượt truy cập theo tháng" : "Lượt truy cập theo ngày"; ?>',
                        data: chartData,
                        backgroundColor: gradient,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointHoverBorderColor: '#fff',
                        pointHitRadius: 10,
                        lineTension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: '<?php echo $view_mode == "monthly" ? "Tháng" : "Ngày"; ?>'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    return `Lượt truy cập: ${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    elements: {
                        line: {
                            tension: 0.4
                        }
                    }
                }
            });
        };

        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            createTrafficChart();
            
            // Handle refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                location.reload();
            });
            
            // Handle PDF download
            document.getElementById('downloadPDFBtn').addEventListener('click', function() {
                const { jsPDF } = window.jspdf;
                html2canvas(document.querySelector('.chart-container')).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('landscape');
                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth() - 20;
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                    
                    // Add header
                    pdf.setFontSize(18);
                    pdf.text('Báo cáo lượt truy cập website', 10, 15);
                    
                    // Add date
                    pdf.setFontSize(12);
                    pdf.text(`Thời gian: ${new Date().toLocaleDateString()}`, 10, 25);
                    
                    // Add stats
                    pdf.text(`Tổng lượt truy cập: ${<?php echo number_format($total_visits); ?>}`, 10, 35);
                    pdf.text(`Lượt truy cập hôm nay: ${<?php echo number_format($today_visits); ?>}`, 10, 45);
                    
                    // Add chart
                    pdf.addImage(imgData, 'PNG', 10, 55, pdfWidth, pdfHeight);
                    
                    // Save the PDF
                    pdf.save(`bao_cao_truy_cap_${new Date().toISOString().slice(0,10)}.pdf`);
                }).catch(error => {
                    console.error('Error generating PDF:', error);
                });
            });
        });

        // Handle form submission for filter
        document.getElementById('trafficFilterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            window.location.href = `?${params}`;
        });
    </script>
</body>
</html>