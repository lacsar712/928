<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>民生气象与空气质量 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .weather-hero {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4a90d9 100%);
            border-radius: var(--card-radius);
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .weather-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }
        .weather-hero .temp-display {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
        }
        .weather-hero .weather-icon {
            font-size: 4rem;
            opacity: 0.9;
        }
        .aqi-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.9rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #000;
        }
        .weather-detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0;
        }
        .chart-container {
            position: relative;
            width: 100%;
        }
        .chart-container canvas {
            width: 100% !important;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-buildings me-2"></i>GovCore 政务平台
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="mayor_mailbox.php">市长信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="budget.php">预决算公开</a></li>
                    <li class="nav-item"><a class="nav-link active" href="weather.php">气象服务</a></li>
                    <li class="nav-item">
                        <a class="nav-link bg-danger rounded px-3 ms-2" href="emergency_report.php">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>应急上报</strong>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                <li class="breadcrumb-item active fw-bold text-gov-blue">民生气象与空气质量</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="weather-hero p-4 shadow" id="todayCard">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75" id="todayCity">XX市</p>
                            <p class="mb-0 small opacity-75" id="todayDate"></p>
                        </div>
                        <i class="bi bi-cloud-sun weather-icon" id="todayIcon"></i>
                    </div>
                    <div class="temp-display mb-1" id="todayTemp">--°</div>
                    <p class="mb-3 opacity-90" id="todayWeather">--</p>
                    <div class="d-flex gap-4 mb-3">
                        <div class="weather-detail-item">
                            <i class="bi bi-droplet-fill"></i>
                            <span id="todayHumidity">--%</span>
                        </div>
                        <div class="weather-detail-item">
                            <i class="bi bi-wind"></i>
                            <span id="todayWind">--</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-3 p-2 rounded" style="background:rgba(255,255,255,0.15);">
                        <span class="fw-bold">AQI</span>
                        <span class="aqi-badge" id="todayAqiBadge" style="background:#FFFF00;">-- --</span>
                        <span class="small opacity-75" id="todayAqiAdvice"></span>
                    </div>
                    <div class="row mt-3 g-2">
                        <div class="col-6">
                            <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.1);">
                                <small class="opacity-75">最高温度</small>
                                <div class="fw-bold" id="todayHigh">--°</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded text-center" style="background:rgba(255,255,255,0.1);">
                                <small class="opacity-75">最低温度</small>
                                <div class="fw-bold" id="todayLow">--°</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gov-blue fw-bold">
                            <i class="bi bi-graph-up me-2"></i>未来 7 天温度预报
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container" style="height:280px;">
                            <canvas id="forecastChart"></canvas>
                        </div>
                        <div class="row mt-3 g-2" id="forecastCards"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gov-blue fw-bold">
                            <i class="bi bi-clock-history me-2"></i>近 24 小时 AQI 变化
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container" style="height:260px;">
                            <canvas id="aqiHourlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 text-gov-blue fw-bold">
                            <i class="bi bi-bar-chart me-2"></i>未来一周 AQI 预报
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container" style="height:260px;">
                            <canvas id="aqiWeeklyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function() {
        function getAqiColor(aqi) {
            if (aqi <= 50) return '#00e400';
            if (aqi <= 100) return '#FFFF00';
            if (aqi <= 150) return '#ff7e00';
            if (aqi <= 200) return '#ff0000';
            if (aqi <= 300) return '#99004c';
            return '#7e0023';
        }

        function getAqiLevel(aqi) {
            if (aqi <= 50) return '优';
            if (aqi <= 100) return '良';
            if (aqi <= 150) return '轻度污染';
            if (aqi <= 200) return '中度污染';
            if (aqi <= 300) return '重度污染';
            return '严重污染';
        }

        function getAqiTextColor(aqi) {
            if (aqi <= 100) return '#000';
            return '#fff';
        }

        var todayData, forecastData, aqiData;

        Promise.all([
            fetch('api/weather/today').then(function(r) { return r.json(); }),
            fetch('api/weather/forecast').then(function(r) { return r.json(); }),
            fetch('api/weather/aqi').then(function(r) { return r.json(); })
        ]).then(function(results) {
            todayData = results[0].data;
            forecastData = results[1].data;
            aqiData = results[2].data;
            renderToday(todayData);
            renderForecast(forecastData);
            renderAqiHourly(aqiData.hourly);
            renderAqiWeekly(aqiData.weekly);
        });

        function renderToday(d) {
            document.getElementById('todayCity').textContent = d.city || 'XX市';
            document.getElementById('todayDate').textContent = d.date + ' ' + d.weather;
            document.getElementById('todayIcon').className = 'bi ' + (d.icon || 'bi-cloud-sun') + ' weather-icon';
            document.getElementById('todayTemp').textContent = d.temp + '°';
            document.getElementById('todayWeather').textContent = d.temp_high + '° / ' + d.temp_low + '°  ' + d.weather;
            document.getElementById('todayHumidity').textContent = d.humidity + '%';
            document.getElementById('todayWind').textContent = d.wind_direction + ' ' + d.wind_scale;
            document.getElementById('todayHigh').textContent = d.temp_high + '°';
            document.getElementById('todayLow').textContent = d.temp_low + '°';

            var badge = document.getElementById('todayAqiBadge');
            badge.textContent = d.aqi + ' ' + d.aqi_level;
            badge.style.background = d.aqi_color;
            badge.style.color = getAqiTextColor(d.aqi);
            document.getElementById('todayAqiAdvice').textContent = d.aqi_advice;
        }

        function renderForecast(list) {
            var labels = list.map(function(d) { return d.date.substring(5); });
            var highs = list.map(function(d) { return d.temp_high; });
            var lows = list.map(function(d) { return d.temp_low; });

            new Chart(document.getElementById('forecastChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '昼间最高温',
                            data: highs,
                            borderColor: '#ff6b6b',
                            backgroundColor: 'rgba(255,107,107,0.1)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 5,
                            pointBackgroundColor: '#ff6b6b'
                        },
                        {
                            label: '夜间最低温',
                            data: lows,
                            borderColor: '#4dabf7',
                            backgroundColor: 'rgba(77,171,247,0.1)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 5,
                            pointBackgroundColor: '#4dabf7'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: '温度 (°C)' },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });

            var cardsHtml = '';
            list.forEach(function(d) {
                cardsHtml += '<div class="col text-center p-2">' +
                    '<div class="small text-muted">' + d.date.substring(5) + '</div>' +
                    '<i class="bi ' + d.day_icon + ' fs-4 text-warning"></i>' +
                    '<div class="small fw-bold">' + d.temp_high + '°</div>' +
                    '<i class="bi ' + d.night_icon + ' fs-5 text-secondary"></i>' +
                    '<div class="small text-muted">' + d.temp_low + '°</div>' +
                    '</div>';
            });
            document.getElementById('forecastCards').innerHTML = cardsHtml;
        }

        function renderAqiHourly(list) {
            var labels = list.map(function(d) { return d.hour; });
            var values = list.map(function(d) { return d.aqi; });
            var colors = list.map(function(d) { return getAqiColor(d.aqi); });

            new Chart(document.getElementById('aqiHourlyChart'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'AQI',
                        data: values,
                        borderColor: '#ff7e00',
                        backgroundColor: 'rgba(255,126,0,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 3,
                        pointBackgroundColor: colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'AQI' },
                            min: 0,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { maxTicksLimit: 12 }
                        }
                    }
                }
            });
        }

        function renderAqiWeekly(list) {
            var labels = list.map(function(d) { return d.date.substring(5); });
            var values = list.map(function(d) { return d.aqi; });
            var colors = list.map(function(d) { return getAqiColor(d.aqi); });

            new Chart(document.getElementById('aqiWeeklyChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'AQI',
                        data: values,
                        backgroundColor: colors,
                        borderRadius: 6,
                        maxBarThickness: 50
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'AQI' },
                            min: 0,
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    })();
    </script>
</body>
</html>
