<?php
require_once 'func.php';

// [VULN] 1. SQL Injection (Search)
$search_sql = "";
$mode = "latest";
if (isset($_GET['keyword'])) {
    $keyword = $_GET['keyword'];
    // [VULN] 直接拼接 SQL，无过滤
    // Payload: ' UNION SELECT 1, user(), database(), 4 -- 
    $sql = "SELECT * FROM news WHERE title LIKE '%$keyword%' ORDER BY publish_date DESC";
    $mode = "search";
    Logger::logAction('Search', "User searched for: $keyword");
} else {
    $sql = "SELECT * FROM news ORDER BY publish_date DESC LIMIT 5";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GovCore 政务公开与应急指挥平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="https://via.placeholder.com/30/ffffff/000000?text=G" alt="" class="d-inline-block align-text-top me-2">
                GovCore 政务平台
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="budget.php">预决算公开</a></li>
                    <li class="nav-item"><a class="nav-link" href="recruit.php">公开招聘</a></li>
                    <li class="nav-item">
                        <a class="nav-link bg-danger rounded px-3 ms-2" href="emergency_report.php">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>应急上报</strong>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link bg-warning text-dark rounded px-3 ms-2 fw-bold" href="booking.php">
                            <i class="bi bi-calendar-check me-1"></i><strong>会议室预约</strong>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Carousel -->
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/banner1.png" class="d-block w-100" alt="数字政府 智慧应急" style="height: 480px; object-fit: cover; filter: brightness(0.8);">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="display-4 fw-bold mb-3 text-shadow">数字政府 智慧应急</h1>
                    <p class="lead mb-4 text-shadow">全面提升政府治理现代化水平</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="assets/img/banner2.png" class="d-block w-100" alt="权威发布 透明高效" style="height: 480px; object-fit: cover; filter: brightness(0.8);">
                <div class="carousel-caption d-none d-md-block">
                    <h1 class="display-4 fw-bold mb-3 text-shadow">权威发布 透明高效</h1>
                    <p class="lead mb-4 text-shadow">打造人民满意的服务型政府</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <!-- News Section -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0 border-start border-4 border-danger ps-3 text-gov-blue fw-bold">
                            <?php echo ($mode == 'search') ? '搜索结果' : '通知公告'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Search Bar -->
                        <form action="index.php" method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="keyword" class="form-control" placeholder="请输入关键字查询..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                                <button class="btn btn-gov-blue" type="submit">搜索</button>
                            </div>
                        </form>

                        <!-- News List -->
                        <div class="list-group list-group-flush">
                            <?php
                            if ($result) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo '<a href="javascript:void(0);" class="list-group-item list-group-item-action py-3 news-item" data-title="' . htmlspecialchars($row['title']) . '" data-date="' . date('Y-m-d', strtotime($row['publish_date'])) . '">';
                                    echo '<div class="d-flex w-100 justify-content-between">';
                                    echo '<h6 class="mb-1 text-dark fw-bold">' . htmlspecialchars($row['title']) . '</h6>';
                                    echo '<small class="text-muted">' . date('Y-m-d', strtotime($row['publish_date'])) . '</small>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            } else {
                                echo '<div class="alert alert-danger">系统错误: ' . mysqli_error($conn) . '</div>'; // Detailed error helps SQLi
                            }
                            ?>
                        </div>
                    </div>
                </div>
            
            <!-- Policy Documents Section -->
            <div class="card border-0 shadow-sm rounded-3 mt-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0 border-start border-4 border-primary ps-3 text-gov-blue fw-bold">
                            政策文件
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php
                            $upload_dir = 'uploads/';
                            if (is_dir($upload_dir)) {
                                $files = scandir($upload_dir);
                                $found_files = false;
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                                        $found_files = true;
                                        echo '<a href="uploads/' . htmlspecialchars($file) . '" class="list-group-item list-group-item-action py-2" target="_blank">';
                                        echo '<div class="d-flex w-100 justify-content-between align-items-center">';
                                        echo '<span class="text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>' . htmlspecialchars($file) . '</span>';
                                        echo '<small class="text-muted">下载</small>';
                                        echo '</div>';
                                        echo '</a>';
                                    }
                                }
                                if (!$found_files) {
                                    echo '<div class="text-center text-muted py-3">暂无政策文件</div>';
                                }
                            } else {
                                echo '<div class="text-center text-muted py-3">暂无政策文件</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
 <!-- Sidebar -->
            <div class="col-md-4">
                <!-- 应急上报入口 -->
                <div class="card border-0 shadow-sm mb-4 bg-gradient-to-r from-danger to-danger">
                    <div class="card-body p-4 text-white" style="background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle-fill display-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">突发事件应急上报</h5>
                                <small class="opacity-75">快速响应 · 及时处置</small>
                            </div>
                        </div>
                        <p class="mb-3 small opacity-90">如遇自然灾害、事故灾难、公共卫生或社会安全事件，请立即上报。</p>
                        <a href="emergency_report.php" class="btn btn-light text-danger fw-bold w-100">
                            <i class="bi bi-send-fill me-2"></i>立即上报
                        </a>
                    </div>
                </div>

                <!-- 会议室预约入口 -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-calendar-check display-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">会议室预约系统</h5>
                                <small class="opacity-75">高效管理 · 便捷预约</small>
                            </div>
                        </div>
                        <p class="mb-3 small opacity-90">在线预约会议室，查看实时占用情况，支持按周日历视图，便捷高效。</p>
                        <a href="booking.php" class="btn btn-light fw-bold w-100" style="color: #667eea;">
                            <i class="bi bi-calendar-plus me-2"></i>立即预约
                        </a>
                    </div>
                </div>

                <!-- 便民服务 -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4 text-white" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-briefcase-fill display-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">公开招聘</h5>
                                <small class="opacity-75">公平公正 · 广纳贤才</small>
                            </div>
                        </div>
                        <p class="mb-3 small opacity-90">查看最新招聘岗位信息，在线提交报名申请。</p>
                        <a href="recruit.php" class="btn btn-light fw-bold w-100" style="color: #28a745;">
                            <i class="bi bi-search me-2"></i>查看岗位
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-gov-blue text-white py-3">
                        <h6 class="mb-0">便民服务</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-primary text-start feature-btn" href="booking.php">
                                📅 会议室预约
                            </a>
                            <button class="btn btn-outline-secondary text-start feature-btn" type="button">📝 在线办事申请</button>
                            <button class="btn btn-outline-secondary text-start feature-btn" type="button">🔍 办件进度查询</button>
                            <button class="btn btn-outline-secondary text-start feature-btn" type="button">📞 12345 热线</button>
                        </div>
                    </div>
                </div>
                
            </div>
           
        </div>
    </div>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 border-start border-4 border-info ps-3 text-gov-blue fw-bold">
                <i class="bi bi-cloud-sun me-2"></i>民生气象与空气质量
            </h5>
            <a href="weather.php" class="btn btn-sm btn-outline-primary">查看详情 <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100" style="background:linear-gradient(135deg,#1e3c72 0%,#2a5298 50%,#4a90d9 100%);color:#fff;">
                    <div class="card-body p-4" id="homeWeatherCard">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <p class="mb-0 opacity-75 small" id="homeWCity">XX市</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="display-4 fw-bold" id="homeWTemp">--°</span>
                                    <i class="bi bi-cloud-sun display-4 opacity-75" id="homeWIcon"></i>
                                </div>
                            </div>
                        </div>
                        <p class="mb-2 small opacity-90" id="homeWDesc">--</p>
                        <div class="d-flex gap-3 mb-2 small">
                            <span><i class="bi bi-droplet-fill me-1"></i><span id="homeWHum">--%</span></span>
                            <span><i class="bi bi-wind me-1"></i><span id="homeWWind">--</span></span>
                        </div>
                        <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:rgba(255,255,255,0.15);">
                            <span class="fw-bold">AQI</span>
                            <span class="aqi-badge" id="homeWAqi" style="background:#FFFF00;color:#000;">-- --</span>
                        </div>
                        <p class="mt-2 mb-0 small opacity-75" id="homeWAdvice"></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-graph-up me-1"></i>7天温度预报</h6>
                    </div>
                    <div class="card-body p-3">
                        <div style="height:220px;"><canvas id="homeForecastChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-bar-chart me-1"></i>一周 AQI 预报</h6>
                    </div>
                    <div class="card-body p-3">
                        <div style="height:220px;"><canvas id="homeAqiChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

    <!-- Feature Not Available Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.querySelectorAll('.feature-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                Swal.fire({
                    title: '功能未开放',
                    text: '该功能正在建设中，敬请期待。',
                    icon: 'warning',
                    confirmButtonText: '知道了'
                });
            });
        });

        document.querySelectorAll('.news-item').forEach(item => {
            item.addEventListener('click', function() {
                const title = this.getAttribute('data-title');
                const date = this.getAttribute('data-date');
                Swal.fire({
                    title: title,
                    html: `
                        <p class="text-muted small mb-3">发布日期: ${date}</p>
                        <div class="text-start px-3">
                            <p>这是一个演示新闻条目。在实际系统中，这里将显示完整的新闻内容详情。</p>
                            <p>GovCore 致力于打造高效、透明的政务服务平台。</p>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: '关闭'
                });
            });
        });

        function getAqiColor(aqi) {
            if (aqi <= 50) return '#00e400';
            if (aqi <= 100) return '#FFFF00';
            if (aqi <= 150) return '#ff7e00';
            if (aqi <= 200) return '#ff0000';
            if (aqi <= 300) return '#99004c';
            return '#7e0023';
        }
        function getAqiTextColor(aqi) { return aqi <= 100 ? '#000' : '#fff'; }

        Promise.all([
            fetch('api/weather/today').then(r => r.json()),
            fetch('api/weather/forecast').then(r => r.json()),
            fetch('api/weather/aqi').then(r => r.json())
        ]).then(([today, forecast, aqi]) => {
            const d = today.data;
            document.getElementById('homeWCity').textContent = d.city || 'XX市';
            document.getElementById('homeWTemp').textContent = d.temp + '°';
            document.getElementById('homeWIcon').className = 'bi ' + (d.icon || 'bi-cloud-sun') + ' display-4 opacity-75';
            document.getElementById('homeWDesc').textContent = d.temp_high + '°/' + d.temp_low + '° ' + d.weather;
            document.getElementById('homeWHum').textContent = d.humidity + '%';
            document.getElementById('homeWWind').textContent = d.wind_direction + ' ' + d.wind_scale;
            const badge = document.getElementById('homeWAqi');
            badge.textContent = d.aqi + ' ' + d.aqi_level;
            badge.style.background = d.aqi_color;
            badge.style.color = getAqiTextColor(d.aqi);
            document.getElementById('homeWAdvice').textContent = d.aqi_advice;

            const fc = forecast.data;
            new Chart(document.getElementById('homeForecastChart'), {
                type: 'line',
                data: {
                    labels: fc.map(x => x.date.substring(5)),
                    datasets: [
                        { label: '最高温', data: fc.map(x => x.temp_high), borderColor: '#ff6b6b', backgroundColor: 'rgba(255,107,107,0.1)', fill: false, tension: 0.3, pointRadius: 4, pointBackgroundColor: '#ff6b6b' },
                        { label: '最低温', data: fc.map(x => x.temp_low), borderColor: '#4dabf7', backgroundColor: 'rgba(77,171,247,0.1)', fill: false, tension: 0.3, pointRadius: 4, pointBackgroundColor: '#4dabf7' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } }, scales: { y: { grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } } }
            });

            const wk = aqi.data.weekly;
            new Chart(document.getElementById('homeAqiChart'), {
                type: 'bar',
                data: {
                    labels: wk.map(x => x.date.substring(5)),
                    datasets: [{ label: 'AQI', data: wk.map(x => x.aqi), backgroundColor: wk.map(x => getAqiColor(x.aqi)), borderRadius: 6, maxBarThickness: 40 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 0, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } } }
            });
        });
    </script>
</body>
</html>
