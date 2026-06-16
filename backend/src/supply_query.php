<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>应急物资查询 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .query-hero { background: linear-gradient(135deg, #004d99 0%, #003366 100%); padding: 3rem 0; }
        .result-card { transition: all 0.3s ease; }
        .result-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .dist-row { border-left: 4px solid var(--gov-blue-primary); }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">GovCore 政务平台</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="mayor_mailbox.php">市长信箱</a></li>
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

    <div class="query-hero text-white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>应急物资库存查询</h2>
                    <p class="opacity-75 mb-4">输入物资名称，即可查询库存总量与各仓库分布情况</p>
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="input-group input-group-lg shadow">
                                <span class="input-group-text bg-white"><i class="bi bi-search text-gov-blue"></i></span>
                                <input type="text" class="form-control border-0" id="queryKeyword" placeholder="请输入物资名称，如：帐篷、矿泉水、急救包...">
                                <button class="btn btn-warning fw-bold px-4" onclick="doQuery()"><i class="bi bi-search me-1"></i>查询</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div id="resultArea">
            <div class="text-center text-muted py-5">
                <i class="bi bi-box-seam display-1 mb-3 d-block opacity-25"></i>
                <p class="mb-0">请输入物资名称进行查询</p>
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
        let pieChart = null;

        document.getElementById('queryKeyword').addEventListener('keypress', e => {
            if (e.key === 'Enter') doQuery();
        });

        function doQuery() {
            const keyword = document.getElementById('queryKeyword').value.trim();
            if (!keyword) {
                document.getElementById('resultArea').innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-exclamation-circle display-1 mb-3 d-block opacity-25"></i>
                        <p class="mb-0">请输入物资名称</p>
                    </div>`;
                return;
            }

            fetch(`api/supply?action=stock_query&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json()).then(data => {
                    if (data.code === 200 && data.data) {
                        renderResult(data.data);
                    } else {
                        document.getElementById('resultArea').innerHTML = `
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-1 mb-3 d-block opacity-25"></i>
                                <p class="mb-0">未找到包含"${keyword}"的物资</p>
                            </div>`;
                    }
                });
        }

        function renderResult(data) {
            const distRows = data.distribution.map(d => `
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom dist-row mb-2 bg-white rounded shadow-sm">
                    <div>
                        <i class="bi bi-building me-2 text-gov-blue"></i>
                        <span class="fw-bold">${d.warehouse_name}</span>
                    </div>
                    <div>
                        <span class="fw-bold text-gov-blue fs-5">${d.quantity}</span>
                        <span class="text-muted">${data.unit}</span>
                    </div>
                </div>
            `).join('');

            document.getElementById('resultArea').innerHTML = `
                <div class="row">
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm rounded-3 result-card mb-4">
                            <div class="card-body p-4 text-center">
                                <h5 class="text-gov-blue fw-bold mb-3"><i class="bi bi-box-seam me-2"></i>${data.name}</h5>
                                <div class="mb-2"><span class="badge bg-info bg-opacity-10 text-info me-1">${data.category || '未分类'}</span></div>
                                <div class="my-4">
                                    <p class="text-muted small mb-1">总库存量</p>
                                    <h1 class="fw-bold text-gov-blue display-4">${data.total_quantity}</h1>
                                    <p class="text-muted">${data.unit}</p>
                                </div>
                                <hr>
                                <p class="text-muted small mb-0">共 ${data.distribution.length} 个仓库存储</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            ${distRows}
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm rounded-3 result-card">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-pie-chart me-2"></i>分仓库库存分布</h5>
                            </div>
                            <div class="card-body p-4 d-flex justify-content-center">
                                <div style="max-width: 450px; width: 100%;"><canvas id="pieChartCanvas"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            renderPieChart(data);
        }

        function renderPieChart(data) {
            if (pieChart) { pieChart.destroy(); pieChart = null; }

            const colors = ['#004d99', '#28a745', '#fd7e14', '#6f42c1', '#dc3545', '#20c997', '#ffc107', '#6c757d'];
            const ctx = document.getElementById('pieChartCanvas');
            if (!ctx) return;

            pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.distribution.map(d => d.warehouse_name),
                    datasets: [{
                        data: data.distribution.map(d => d.quantity),
                        backgroundColor: colors.slice(0, data.distribution.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: { size: 13 },
                                usePointStyle: true,
                                pointStyleWidth: 12
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const value = context.parsed;
                                    const pct = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} ${data.unit} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
