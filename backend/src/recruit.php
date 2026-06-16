<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公开招聘 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .position-card {
            border-left: 4px solid var(--gov-blue-primary);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .position-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .position-card.expired {
            border-left-color: #6c757d;
            opacity: 0.75;
        }
        .position-card.urgent {
            border-left-color: #dc3545;
        }
        .countdown-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .countdown-active { background-color: #e8f5e9; color: #2e7d32; }
        .countdown-warning { background-color: #fff3e0; color: #e65100; }
        .countdown-urgent { background-color: #ffebee; color: #c62828; }
        .countdown-expired { background-color: #eceff1; color: #546e7a; }
        .edu-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: #e6f0ff;
            color: #004d99;
        }
        .filter-section {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }
        .hero-section {
            background: linear-gradient(135deg, var(--gov-blue-primary) 0%, var(--gov-blue-dark) 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                GovCore 政务平台
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
                    <li class="nav-item"><a class="nav-link active" href="recruit.php">公开招聘</a></li>
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

    <div class="hero-section">
        <div class="container text-center">
            <h2 class="fw-bold mb-2"><i class="bi bi-briefcase-fill me-2"></i>公开招聘</h2>
            <p class="opacity-75 mb-0">公平公正 · 透明公开 · 广纳贤才</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="filter-section p-4 mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="keyword" placeholder="搜索岗位名称、招聘单位...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="educationFilter">
                        <option value="">全部学历</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="departmentFilter">
                        <option value="">全部单位</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-gov-blue flex-grow-1" onclick="searchPositions()">
                            <i class="bi bi-funnel me-1"></i>筛选
                        </button>
                        <button class="btn btn-outline-secondary flex-grow-1" onclick="resetFilters()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="positionList"></div>

        <div class="text-center py-3" id="pagination"></div>
    </div>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 9;

        function searchPositions() {
            currentPage = 1;
            loadPositions();
        }

        function resetFilters() {
            document.getElementById('keyword').value = '';
            document.getElementById('educationFilter').value = '';
            document.getElementById('departmentFilter').value = '';
            currentPage = 1;
            loadPositions();
        }

        function loadPositions() {
            const keyword = document.getElementById('keyword').value.trim();
            const education = document.getElementById('educationFilter').value;
            const department = document.getElementById('departmentFilter').value;

            fetch(`recruit_api.php?action=list&page=${currentPage}&page_size=${pageSize}&keyword=${encodeURIComponent(keyword)}&education=${encodeURIComponent(education)}&department=${encodeURIComponent(department)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        updateFilters(data.data.educations, data.data.departments);
                        renderList(data.data.list);
                        renderPagination(data.data);
                    }
                });
        }

        function updateFilters(educations, departments) {
            const eduSel = document.getElementById('educationFilter');
            const currentEdu = eduSel.value;
            eduSel.innerHTML = '<option value="">全部学历</option>' + educations.map(e => `<option value="${e}" ${e === currentEdu ? 'selected' : ''}>${e}</option>`).join('');

            const deptSel = document.getElementById('departmentFilter');
            const currentDept = deptSel.value;
            deptSel.innerHTML = '<option value="">全部单位</option>' + departments.map(d => `<option value="${d}" ${d === currentDept ? 'selected' : ''}>${d}</option>`).join('');
        }

        function renderList(list) {
            const container = document.getElementById('positionList');

            if (list.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                        <p class="mb-0">暂无招聘岗位</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = '<div class="row g-4">' + list.map(pos => {
                let countdownClass = 'countdown-active';
                let countdownText = `剩余 ${pos.remaining_days} 天`;
                let cardClass = 'position-card';

                if (pos.is_expired) {
                    countdownClass = 'countdown-expired';
                    countdownText = '已截止';
                    cardClass += ' expired';
                } else if (pos.remaining_days <= 3) {
                    countdownClass = 'countdown-urgent';
                    countdownText = `仅剩 ${pos.remaining_days} 天`;
                    cardClass += ' urgent';
                } else if (pos.remaining_days <= 7) {
                    countdownClass = 'countdown-warning';
                    countdownText = `剩余 ${pos.remaining_days} 天`;
                }

                return `
                    <div class="col-md-4">
                        <div class="card ${cardClass} border-0 shadow-sm h-100" onclick="window.location.href='recruit_detail.php?id=${pos.id}'">
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold text-gov-blue mb-2">${escapeHtml(pos.title)}</h5>
                                <p class="text-muted mb-2"><i class="bi bi-building me-1"></i>${escapeHtml(pos.department)}</p>
                                <div class="mb-2">
                                    <span class="edu-badge">${escapeHtml(pos.education)}</span>
                                    <span class="ms-2 text-muted small"><i class="bi bi-people me-1"></i>${pos.headcount} 人</span>
                                </div>
                                <div class="mt-auto pt-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i>截止 ${pos.deadline}</span>
                                    <span class="countdown-badge ${countdownClass}">${countdownText}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('') + '</div>';
        }

        function renderPagination(data) {
            const container = document.getElementById('pagination');
            if (data.total_pages <= 1) {
                container.innerHTML = `<span class="text-muted small">共 ${data.total} 个岗位</span>`;
                return;
            }

            let html = `<nav class="d-flex justify-content-center align-items-center gap-3"><span class="text-muted small">共 ${data.total} 个岗位</span><ul class="pagination mb-0">`;
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
            loadPositions();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s || '';
            return d.innerHTML;
        }

        document.getElementById('keyword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchPositions();
        });

        loadPositions();
    </script>

</body>
</html>
