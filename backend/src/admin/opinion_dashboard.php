<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>舆情监测大屏 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/opinion-dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="opinion-dark">

    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-radar me-2"></i>GovCore 舆情监测中心
            </a>
            <span class="navbar-text text-white">
                <span class="pulse-dot"></span>
                <span id="currentTime" class="me-3"></span>
                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | 
                <a href="logout.php" class="text-decoration-none">退出</a>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>返回控制台
                    </a>
                    <a href="budget.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-stack me-2"></i>预决算管理
                    </a>
                    <a href="mail.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope-open me-2"></i>意见信箱
                    </a>
                    <a href="mayor_mailbox.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-buildings me-2"></i>市长信箱
                    </a>
                    <a href="mail_keywords.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-exclamation me-2"></i>敏感词管理
                    </a>
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-display me-2"></i>监测大屏
                    </a>
                    <a href="opinion_keywords.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-tags-fill me-2"></i>关键词配置
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light grid-bg">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">舆情监测大屏</li>
                        </ol>
                    </nav>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">
                                    <i class="bi bi-graph-up-arrow me-1"></i>今日抓取条数
                                </div>
                                <div class="stat-value" id="statToday">0</div>
                                <i class="bi bi-database-fill-gear stat-icon"></i>
                                <div class="small mt-2" id="todayTrendBox">
                                    <i class="bi bi-dash me-1"></i>较昨日 <span id="todayTrend">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card negative">
                                <div class="stat-label">
                                    <i class="bi bi-exclamation-triangle me-1"></i>负面比例
                                </div>
                                <div class="stat-value" id="statNegativeRatio">0%</div>
                                <i class="bi bi-sign-danger-fill stat-icon"></i>
                                <div class="small mt-2" id="negativeTrendBox">
                                    <i class="bi bi-dash me-1"></i>较昨日 <span id="negativeTrend">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card matched">
                                <div class="stat-label">
                                    <i class="bi bi-search-heart me-1"></i>命中关键词数
                                </div>
                                <div class="stat-value" id="statMatched">0</div>
                                <i class="bi bi-key-fill stat-icon"></i>
                                <div class="text-muted small mt-2">
                                    <i class="bi bi-hash me-1"></i>覆盖 <span id="keywordCoverage">0</span> 个关键词
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-5">
                            <div class="chart-card tech-border">
                                <h6 class="chart-title">情感占比分布</h6>
                                <div class="canvas-container">
                                    <canvas id="sentimentPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="chart-card tech-border">
                                <h6 class="chart-title">来源平台分布</h6>
                                <div class="canvas-container">
                                    <canvas id="platformBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card tech-border mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="opinionSearch" placeholder="搜索舆情标题或内容...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="sentimentFilter">
                                        <option value="0">全部情感</option>
                                        <option value="1">正向</option>
                                        <option value="2">中性</option>
                                        <option value="3">负向</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="sourceFilter">
                                        <option value="">全部平台</option>
                                        <option value="微博">微博</option>
                                        <option value="微信公众号">微信公众号</option>
                                        <option value="抖音">抖音</option>
                                        <option value="快手">快手</option>
                                        <option value="B站">B站</option>
                                        <option value="知乎">知乎</option>
                                        <option value="小红书">小红书</option>
                                        <option value="今日头条">今日头条</option>
                                        <option value="百度贴吧">百度贴吧</option>
                                        <option value="豆瓣">豆瓣</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="loadAllData()">
                                            <i class="bi bi-arrow-repeat me-1"></i>刷新数据
                                        </button>
                                        <button class="btn btn-primary" onclick="forceGenerate()">
                                            <i class="bi bi-plus-circle me-1"></i>模拟抓取
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-newspaper me-2"></i>舆情条目
                            </h5>
                            <span class="badge bg-secondary" id="totalCount">共 0 条</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">ID</th>
                                            <th>标题</th>
                                            <th style="width: 100px;">来源</th>
                                            <th style="width: 100px;">情感</th>
                                            <th>命中关键词</th>
                                            <th style="width: 160px;">发布时间</th>
                                        </tr>
                                    </thead>
                                    <tbody id="opinionList">
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center py-4" id="pagination">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detailTitle">舆情详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">关闭</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 10;
        let sentimentPieChart = null;
        let platformBarChart = null;
        let hasGenerated = false;

        const sentimentLabels = {
            1: { text: '正向', cls: 'positive' },
            2: { text: '中性', cls: 'neutral' },
            3: { text: '负向', cls: 'negative' }
        };

        const platformColors = {
            '微博': '#E6162D',
            '微信公众号': '#07C160',
            '抖音': '#000000',
            '快手': '#FF4906',
            'B站': '#FB7299',
            '知乎': '#0084FF',
            '小红书': '#FE2C55',
            '今日头条': '#FF0000',
            '百度贴吧': '#2319DC',
            '豆瓣': '#41AC52'
        };

        function updateCurrentTime() {
            const now = new Date();
            const timeStr = now.toLocaleString('zh-CN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            document.getElementById('currentTime').textContent = timeStr;
        }

        function loadAllData() {
            loadStats();
            loadOpinions();
        }

        function formatTrend(diff, isPercentage = false, isInverse = false) {
            const prefix = diff > 0 ? '+' : '';
            const value = isPercentage ? diff.toFixed(1) + '%' : diff.toFixed(1) + '%';
            const icon = diff > 0 ? 'bi-arrow-up' : (diff < 0 ? 'bi-arrow-down' : 'bi-dash');
            const trendClass = isInverse
                ? (diff > 0 ? 'trend-bad' : (diff < 0 ? 'trend-good' : 'trend-flat'))
                : (diff > 0 ? 'trend-good' : (diff < 0 ? 'trend-bad' : 'trend-flat'));
            return {
                text: prefix + value,
                icon: icon,
                class: trendClass
            };
        }

        function updateTrendElement(elementId, boxId, diff, hasYesterdayData, isInverse) {
            const element = document.getElementById(elementId);
            const box = document.getElementById(boxId);

            if (!hasYesterdayData) {
                element.textContent = '--';
                box.className = 'text-muted small mt-2';
                const iconElement = box.querySelector('i');
                if (iconElement) {
                    iconElement.className = 'bi bi-dash me-1';
                }
                return;
            }

            const trend = formatTrend(diff, true, isInverse);
            element.textContent = trend.text;
            box.className = 'small mt-2 ' + trend.class;

            const iconElement = box.querySelector('i');
            if (iconElement) {
                iconElement.className = 'bi ' + trend.icon + ' me-1';
            }
        }

        function loadStats() {
            fetch('opinion_api.php?action=stats')
                .then(response => response.json())
                .then(data => {
                    if (data.code === 200) {
                        const stats = data.data;
                        const hasYesterday = stats.yesterday_negative_ratio !== undefined;

                        animateNumber('statToday', stats.today_count);
                        animateNumber('statNegativeRatio', stats.negative_ratio, '%');
                        animateNumber('statMatched', stats.matched_keywords_count);
                        document.getElementById('keywordCoverage').textContent = stats.keyword_coverage || 0;

                        updateTrendElement('todayTrend', 'todayTrendBox', stats.today_count_diff || 0, hasYesterday, false);
                        updateTrendElement('negativeTrend', 'negativeTrendBox', stats.negative_ratio_diff || 0, hasYesterday, true);

                        renderPieChart(stats.sentiment);
                        renderBarChart(stats.platforms);
                    }
                });
        }

        function animateNumber(elementId, target, suffix = '') {
            const element = document.getElementById(elementId);
            const duration = 1000;
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = (typeof target === 'number' && target % 1 !== 0 
                    ? current.toFixed(1) 
                    : Math.floor(current)) + suffix;
            }, 16);
        }

        function renderPieChart(sentimentData) {
            const ctx = document.getElementById('sentimentPieChart').getContext('2d');

            if (sentimentPieChart) {
                sentimentPieChart.destroy();
            }

            const total = sentimentData.positive + sentimentData.neutral + sentimentData.negative;

            sentimentPieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['正向', '中性', '负向'],
                    datasets: [{
                        data: [sentimentData.positive, sentimentData.neutral, sentimentData.negative],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#e2e8f0',
                                font: { size: 12 },
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#94a3b8',
                            borderColor: '#06b6d4',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%',
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1500
                    }
                }
            });
        }

        function renderBarChart(platformData) {
            const ctx = document.getElementById('platformBarChart').getContext('2d');

            if (platformBarChart) {
                platformBarChart.destroy();
            }

            const labels = platformData.map(p => p.platform);
            const data = platformData.map(p => p.count);
            const colors = platformData.map(p => platformColors[p.platform] || '#3b82f6');

            platformBarChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '舆情数量',
                        data: data,
                        backgroundColor: colors.map(c => c + 'CC'),
                        borderColor: colors,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#94a3b8',
                            borderColor: '#06b6d4',
                            borderWidth: 1,
                            padding: 12
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(45, 55, 72, 0.5)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 }
                            }
                        },
                        y: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#e2e8f0',
                                font: { size: 11 }
                            }
                        }
                    },
                    animation: {
                        duration: 1500
                    }
                }
            });
        }

        function loadOpinions() {
            const sentiment = document.getElementById('sentimentFilter').value;
            const source = document.getElementById('sourceFilter').value;
            const keyword = document.getElementById('opinionSearch').value;

            fetch(`opinion_api.php?action=opinion_list&page=${currentPage}&page_size=${pageSize}&sentiment=${sentiment}&source=${encodeURIComponent(source)}&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 200) {
                        renderOpinionList(data.data.list);
                        renderPagination(data.data);
                        document.getElementById('totalCount').textContent = `共 ${data.data.total} 条`;
                    }
                });
        }

        function renderOpinionList(opinions) {
            const container = document.getElementById('opinionList');

            if (opinions.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="6">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                                <p class="mb-0">暂无舆情数据</p>
                                <button class="btn btn-primary mt-3" onclick="forceGenerate()">
                                    <i class="bi bi-plus-circle me-1"></i>点击生成模拟数据
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            container.innerHTML = opinions.map(opinion => {
                let keywordHtml = '';
                if (opinion.matched_keywords_arr && opinion.matched_keywords_arr.length > 0) {
                    keywordHtml = opinion.matched_keywords_arr.map(kw => {
                        const sentCls = kw.sentiment === 1 ? 'positive' : (kw.sentiment === 3 ? 'negative' : '');
                        return `<span class="keyword-highlight ${sentCls}">${kw.keyword}</span>`;
                    }).join('');
                } else {
                    keywordHtml = '<span class="text-muted small">无</span>';
                }

                return `
                    <tr style="cursor: pointer;" onclick="showDetail(${opinion.id})">
                        <td class="font-monospace text-muted">#${opinion.id}</td>
                        <td>
                            <div class="opinion-table-title">${escapeHtml(opinion.title)}</div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-person me-1"></i>${escapeHtml(opinion.author || '匿名')}
                            </div>
                        </td>
                        <td>
                            <span class="source-badge">
                                <i class="bi bi-globe me-1"></i>${opinion.source_platform}
                            </span>
                        </td>
                        <td>
                            <span class="sentiment-tag sentiment-${sentimentLabels[opinion.sentiment].cls}">
                                ${sentimentLabels[opinion.sentiment].text}
                            </span>
                        </td>
                        <td>${keywordHtml}</td>
                        <td class="text-muted small">
                            <i class="bi bi-clock me-1"></i>${opinion.publish_time}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderPagination(data) {
            const container = document.getElementById('pagination');
            if (data.total_pages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '<nav><ul class="pagination justify-content-center">';
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="goToPage(${currentPage - 1})">上一页</a></li>`;

            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<li class="page-item ${currentPage === i ? 'active' : ''}"><a class="page-link" onclick="goToPage(${i})">${i}</a></li>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            html += `<li class="page-item ${currentPage === data.total_pages ? 'disabled' : ''}"><a class="page-link" onclick="goToPage(${currentPage + 1})">下一页</a></li>`;
            html += '</ul></nav>';

            container.innerHTML = html;
        }

        function goToPage(page) {
            currentPage = page;
            loadOpinions();
        }

        function resetFilters() {
            document.getElementById('opinionSearch').value = '';
            document.getElementById('sentimentFilter').value = 0;
            document.getElementById('sourceFilter').value = '';
            currentPage = 1;
            loadOpinions();
        }

        function forceGenerate() {
            Swal.fire({
                title: '模拟抓取中...',
                text: '正在生成新的舆情数据，请稍候',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('opinion_api.php?action=generate', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.code === 200) {
                        Swal.fire({
                            title: '抓取成功',
                            text: data.msg,
                            icon: 'success',
                            confirmButtonText: '确定',
                            timer: 2000
                        });
                        loadAllData();
                    } else {
                        Swal.fire({
                            title: '抓取失败',
                            text: data.msg,
                            icon: 'error',
                            confirmButtonText: '确定'
                        });
                    }
                });
        }

        function showDetail(id) {
            fetch(`opinion_api.php?action=opinion_list&page=1&page_size=1&keyword=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 200 && data.data.list.length > 0) {
                        const opinion = data.data.list[0];

                        let keywordHtml = '';
                        if (opinion.matched_keywords_arr && opinion.matched_keywords_arr.length > 0) {
                            keywordHtml = opinion.matched_keywords_arr.map(kw => {
                                const sentCls = kw.sentiment === 1 ? 'positive' : (kw.sentiment === 3 ? 'negative' : 'neutral');
                                return `<span class="keyword-tag ${sentCls}">${kw.keyword} <small class="opacity-75">(权重:${kw.weight})</small></span>`;
                            }).join('');
                        } else {
                            keywordHtml = '<span class="text-muted">无匹配关键词</span>';
                        }

                        document.getElementById('detailTitle').textContent = opinion.title;
                        document.getElementById('detailContent').innerHTML = `
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="p-3 rounded" style="background: var(--dark-bg-tertiary);">
                                        <small class="text-muted d-block mb-1">来源平台</small>
                                        <span class="source-badge"><i class="bi bi-globe me-1"></i>${opinion.source_platform}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded" style="background: var(--dark-bg-tertiary);">
                                        <small class="text-muted d-block mb-1">情感倾向</small>
                                        <span class="sentiment-tag sentiment-${sentimentLabels[opinion.sentiment].cls}">
                                            ${sentimentLabels[opinion.sentiment].text}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 rounded" style="background: var(--dark-bg-tertiary);">
                                        <small class="text-muted d-block mb-1">发布作者</small>
                                        <span class="fw-bold"><i class="bi bi-person me-1"></i>${escapeHtml(opinion.author || '匿名')}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <small class="text-muted d-block mb-2 fw-bold">命中关键词</small>
                                ${keywordHtml}
                            </div>
                            <div class="mb-4">
                                <small class="text-muted d-block mb-2 fw-bold">舆情内容</small>
                                <div class="p-4 rounded" style="background: var(--dark-bg-tertiary); line-height: 1.8;">
                                    ${escapeHtml(opinion.content)}
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">发布时间</small>
                                    <span><i class="bi bi-clock me-1"></i>${opinion.publish_time}</span>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted d-block mb-1">抓取时间</small>
                                    <span><i class="bi bi-download me-1"></i>${opinion.crawl_time}</span>
                                </div>
                            </div>
                        `;

                        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                        modal.show();
                    }
                });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.getElementById('opinionSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadOpinions();
            }
        });

        document.getElementById('sentimentFilter').addEventListener('change', function() {
            currentPage = 1;
            loadOpinions();
        });

        document.getElementById('sourceFilter').addEventListener('change', function() {
            currentPage = 1;
            loadOpinions();
        });

        updateCurrentTime();
        setInterval(updateCurrentTime, 1000);

        fetch('opinion_api.php?action=generate', { method: 'POST' })
            .then(response => response.json())
            .then(() => {
                hasGenerated = true;
                loadAllData();
            });

        setInterval(() => {
            loadStats();
        }, 30000);
    </script>

</body>
</html>
