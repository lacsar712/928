<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>部门预决算公开 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .year-tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 8px;
            display: flex;
            gap: 4px;
            overflow-x: auto;
        }
        .year-tab {
            flex: 1;
            min-width: 80px;
            padding: 12px 20px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
            white-space: nowrap;
            cursor: pointer;
        }
        .year-tab:hover { background: #f8f9fa; color: #004d99; }
        .year-tab.active {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0,77,153,0.3);
        }
        .budget-table th {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 14px 16px;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s;
        }
        .budget-table th:hover {
            background: linear-gradient(135deg, #003366 0%, #002244 100%);
        }
        .budget-table th .sort-icon {
            opacity: 0.4;
            margin-left: 4px;
            font-size: 0.75rem;
        }
        .budget-table th.sorted .sort-icon {
            opacity: 1;
            color: #ffc107;
        }
        .budget-table td {
            padding: 12px 16px;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }
        .budget-table tbody tr {
            transition: all 0.2s;
        }
        .budget-table tbody tr:hover {
            background-color: #e6f0ff !important;
        }
        .amount-cell {
            font-family: 'Courier New', monospace;
            font-weight: 500;
            text-align: right;
        }
        .diff-rate-cell {
            font-weight: 700;
            text-align: center;
        }
        .diff-warning {
            background-color: #fff3cd !important;
            color: #856404;
            animation: pulse-warning 2s infinite;
        }
        .diff-danger {
            background-color: #f8d7da !important;
            color: #721c24;
            animation: pulse-danger 2s infinite;
        }
        @keyframes pulse-warning {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
        }
        @keyframes pulse-danger {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        .diff-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .diff-normal { background: #e8f5e9; color: #2e7d32; }
        .diff-warn { background: #fff3e0; color: #e65100; }
        .diff-alert { background: #ffebee; color: #c62828; }
        .summary-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .dept-highlight {
            background-color: #fffde7;
        }
        .no-data-state {
            text-align: center;
            padding: 60px 20px;
            color: #adb5bd;
        }
        .no-data-state .bi { font-size: 4rem; opacity: 0.5; margin-bottom: 16px; display: block; }
    </style>
</head>
<body class="bg-light">

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
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="leaders.php">领导信息</a></li>
                    <li class="nav-item"><a class="nav-link active" href="budget.php">预决算公开</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="mayor_mailbox.php">市长信箱</a></li>
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

    <div class="py-4" style="background: linear-gradient(135deg, #004d99 0%, #003366 100%);">
        <div class="container">
            <div class="text-white">
                <h2 class="fw-bold mb-2"><i class="bi bi-cash-stack me-2"></i>部门预决算公开</h2>
                <p class="mb-0 opacity-80">阳光财政，透明运行，依法公开部门预决算信息</p>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="year-tabs mb-4" id="yearTabs">
        </div>

        <div class="row mb-4" id="summaryCards">
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-gov-blue fw-bold">
                    <i class="bi bi-table me-2"></i><span id="tableTitle">预决算明细</span>
                </h5>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="input-group" style="width: 260px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="按部门名称搜索...">
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="downloadExport('csv')">
                            <i class="bi bi-filetype-csv me-1"></i>CSV
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="downloadExport('xls')">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 budget-table" id="budgetTable">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th data-field="department" onclick="toggleSort('department')">
                                    部门名称 <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th data-field="budget_income" onclick="toggleSort('budget_income')" class="text-end">
                                    预算收入(万元) <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th data-field="budget_expenditure" onclick="toggleSort('budget_expenditure')" class="text-end">
                                    预算支出(万元) <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th data-field="final_income" onclick="toggleSort('final_income')" class="text-end">
                                    决算收入(万元) <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th data-field="final_expenditure" onclick="toggleSort('final_expenditure')" class="text-end">
                                    决算支出(万元) <i class="bi bi-arrow-down-up sort-icon"></i>
                                </th>
                                <th class="text-center" style="width: 120px;">差额率(%)</th>
                            </tr>
                        </thead>
                        <tbody id="budgetBody">
                        </tbody>
                    </table>
                </div>
                <div id="noData" class="no-data-state d-none">
                    <i class="bi bi-inbox"></i>
                    <h5>暂无数据</h5>
                    <p class="mb-0 small">该年度暂无预决算公开数据</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <p class="mb-0 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    差额率 = |决算支出 - 预算支出| / 预算支出 × 100%。差额率超过 10% 显示橙色警示，超过 20% 显示红色警示。仅当预算支出与决算支出均大于 0 时计算差额率。
                </p>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentYear = new Date().getFullYear();
        let currentSortField = '';
        let currentSortOrder = 'ASC';
        let currentKeyword = '';
        let allData = [];
        const WARN_THRESHOLD = 10;
        const DANGER_THRESHOLD = 20;

        function init() {
            loadYears();
        }

        function loadYears() {
            fetch('budget_api.php?action=years')
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderYearTabs(data.data);
                        if (data.data.length > 0) {
                            currentYear = data.data[0];
                        }
                        loadBudgetData();
                    }
                });
        }

        function renderYearTabs(years) {
            const container = document.getElementById('yearTabs');
            container.innerHTML = years.map(y => `
                <button class="year-tab ${y === currentYear ? 'active' : ''}" onclick="switchYear(${y}, this)">
                    ${y} 年度
                </button>
            `).join('');
        }

        function switchYear(year, btn) {
            currentYear = year;
            document.querySelectorAll('.year-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            currentSortField = '';
            currentSortOrder = 'ASC';
            currentKeyword = '';
            document.getElementById('searchInput').value = '';
            loadBudgetData();
        }

        function loadBudgetData() {
            const params = new URLSearchParams({
                action: 'list',
                year: currentYear,
                keyword: currentKeyword,
                sort_field: currentSortField,
                sort_order: currentSortOrder
            });

            fetch('budget_api.php?' + params)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        allData = data.data.list;
                        renderTable(allData);
                        renderSummary(allData);
                        document.getElementById('tableTitle').textContent = `${currentYear} 年度预决算明细`;
                        updateSortIcons();
                    }
                });
        }

        function renderTable(list) {
            const tbody = document.getElementById('budgetBody');
            const noData = document.getElementById('noData');

            if (list.length === 0) {
                tbody.innerHTML = '';
                noData.classList.remove('d-none');
                return;
            }

            noData.classList.add('d-none');

            tbody.innerHTML = list.map((row, index) => {
                const diffRate = row.diff_rate;
                let diffHtml = '<span class="text-muted">-</span>';
                let rowClass = '';

                if (diffRate !== null) {
                    let badgeClass = 'diff-normal';
                    if (diffRate >= DANGER_THRESHOLD) {
                        badgeClass = 'diff-alert';
                        rowClass = 'diff-danger';
                    } else if (diffRate >= WARN_THRESHOLD) {
                        badgeClass = 'diff-warn';
                        rowClass = 'diff-warning';
                    }
                    diffHtml = `<span class="diff-badge ${badgeClass}">${diffRate}%</span>`;
                }

                const deptClass = currentKeyword && row.department.includes(currentKeyword) ? 'dept-highlight' : '';

                return `
                    <tr class="${rowClass}">
                        <td class="text-muted">${index + 1}</td>
                        <td class="fw-bold ${deptClass}">${escapeHtml(row.department)}</td>
                        <td class="amount-cell">${formatAmount(row.budget_income)}</td>
                        <td class="amount-cell">${formatAmount(row.budget_expenditure)}</td>
                        <td class="amount-cell">${formatAmount(row.final_income)}</td>
                        <td class="amount-cell">${formatAmount(row.final_expenditure)}</td>
                        <td class="diff-rate-cell">${diffHtml}</td>
                    </tr>
                `;
            }).join('');
        }

        function renderSummary(list) {
            const container = document.getElementById('summaryCards');
            let totalBudgetIncome = 0, totalBudgetExpenditure = 0;
            let totalFinalIncome = 0, totalFinalExpenditure = 0;
            let warnCount = 0, dangerCount = 0;

            list.forEach(row => {
                totalBudgetIncome += parseFloat(row.budget_income) || 0;
                totalBudgetExpenditure += parseFloat(row.budget_expenditure) || 0;
                totalFinalIncome += parseFloat(row.final_income) || 0;
                totalFinalExpenditure += parseFloat(row.final_expenditure) || 0;
                if (row.diff_rate !== null) {
                    if (row.diff_rate >= DANGER_THRESHOLD) dangerCount++;
                    else if (row.diff_rate >= WARN_THRESHOLD) warnCount++;
                }
            });

            container.innerHTML = `
                <div class="col-md-3 mb-3">
                    <div class="card summary-card border-0 shadow-sm rounded-3 h-100" style="border-left-color: #004d99 !important;">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">预算收入合计</p>
                                <h4 class="mb-0 fw-bold text-gov-blue">${formatAmount(totalBudgetIncome)}</h4>
                                <small class="text-muted">万元</small>
                            </div>
                            <div class="rounded-circle p-3" style="background: rgba(0,77,153,0.1); color: #004d99;">
                                <i class="bi bi-graph-up-arrow fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card summary-card border-0 shadow-sm rounded-3 h-100" style="border-left-color: #28a745 !important;">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">预算支出合计</p>
                                <h4 class="mb-0 fw-bold text-success">${formatAmount(totalBudgetExpenditure)}</h4>
                                <small class="text-muted">万元</small>
                            </div>
                            <div class="rounded-circle p-3" style="background: rgba(40,167,69,0.1); color: #28a745;">
                                <i class="bi bi-graph-down-arrow fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card summary-card border-0 shadow-sm rounded-3 h-100" style="border-left-color: #fd7e14 !important;">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">差额率警示</p>
                                <h4 class="mb-0 fw-bold" style="color: #fd7e14;">${warnCount} <small class="text-muted fw-normal">橙色</small></h4>
                            </div>
                            <div class="rounded-circle p-3" style="background: rgba(253,126,20,0.1); color: #fd7e14;">
                                <i class="bi bi-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card summary-card border-0 shadow-sm rounded-3 h-100" style="border-left-color: #dc3545 !important;">
                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                            <div>
                                <p class="text-muted small mb-1 fw-bold">差额率严重</p>
                                <h4 class="mb-0 fw-bold text-danger">${dangerCount} <small class="text-muted fw-normal">红色</small></h4>
                            </div>
                            <div class="rounded-circle p-3" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                                <i class="bi bi-shield-exclamation fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function toggleSort(field) {
            if (currentSortField === field) {
                currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSortField = field;
                currentSortOrder = 'ASC';
            }
            loadBudgetData();
        }

        function updateSortIcons() {
            document.querySelectorAll('.budget-table th').forEach(th => {
                const field = th.dataset.field;
                th.classList.remove('sorted');
                const icon = th.querySelector('.sort-icon');
                if (icon) {
                    icon.className = 'bi bi-arrow-down-up sort-icon';
                }
                if (field === currentSortField) {
                    th.classList.add('sorted');
                    if (icon) {
                        icon.className = currentSortOrder === 'ASC'
                            ? 'bi bi-arrow-up sort-icon'
                            : 'bi bi-arrow-down sort-icon';
                    }
                }
            });
        }

        function downloadExport(format) {
            window.open(`budget_api.php?action=export&year=${currentYear}&format=${format}`, '_blank');
        }

        function formatAmount(val) {
            const num = parseFloat(val) || 0;
            return num.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s || '';
            return d.innerHTML;
        }

        document.getElementById('searchInput').addEventListener('input', function() {
            currentKeyword = this.value.trim();
            loadBudgetData();
        });

        init();
    </script>
</body>
</html>
