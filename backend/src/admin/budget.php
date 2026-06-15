<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>预决算数据管理 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .budget-table th {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 14px;
            white-space: nowrap;
        }
        .budget-table td {
            padding: 10px 14px;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }
        .budget-table tbody tr:hover {
            background-color: #e6f0ff !important;
        }
        .amount-cell {
            font-family: 'Courier New', monospace;
            font-weight: 500;
            text-align: right;
        }
        .diff-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .diff-normal { background: #e8f5e9; color: #2e7d32; }
        .diff-warn { background: #fff3e0; color: #e65100; }
        .diff-alert { background: #ffebee; color: #c62828; }
        .diff-warning-row { background-color: #fff8e1 !important; }
        .diff-danger-row { background-color: #fff0f0 !important; }
        .upload-zone {
            border: 2px dashed #ced4da;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-zone:hover, .upload-zone.dragover {
            border-color: #004d99;
            background-color: #e6f0ff;
        }
        .upload-zone .bi {
            font-size: 3rem;
            color: #004d99;
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">GovCore 管理中心</a>
            <span class="navbar-text text-white">
                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a href="logout.php" class="text-white-50 text-decoration-none">退出</a>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                    <a href="emergency.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>应急事件
                    </a>
                    <a href="supply.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-box-seam me-2"></i>物资台账
                    </a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-upload me-2"></i>政策文件上传
                    </a>
                    <a href="meeting_rooms.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-door-open me-2"></i>会议室管理
                    </a>
                    <a href="budget.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-cash-stack me-2"></i>预决算管理
                    </a>
                    <a href="mail.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope-open me-2"></i>意见信箱
                    </a>
                    <a href="mail_keywords.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-exclamation me-2"></i>敏感词管理
                    </a>
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-radar me-2"></i>舆情监测看板
                    </a>
                    <a href="weather_config.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-sun me-2"></i>气象数据源
                    </a>
                    <a href="recruit.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-briefcase me-2"></i>招聘管理
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">预决算数据管理</li>
                        </ol>
                    </nav>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-plus-circle me-2"></i>手工新增</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form id="addForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">年度</label>
                                                <select class="form-select" id="addYear" required></select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">部门名称</label>
                                                <input type="text" class="form-control" id="addDepartment" placeholder="如：市教育局" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">预算收入(万元)</label>
                                                <input type="number" step="0.01" class="form-control" id="addBudgetIncome" value="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">预算支出(万元)</label>
                                                <input type="number" step="0.01" class="form-control" id="addBudgetExpenditure" value="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">决算收入(万元)</label>
                                                <input type="number" step="0.01" class="form-control" id="addFinalIncome" value="0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">决算支出(万元)</label>
                                                <input type="number" step="0.01" class="form-control" id="addFinalExpenditure" value="0" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-gov-blue mt-3 px-4">
                                            <i class="bi bi-plus-lg me-1"></i>新增记录
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i>Excel 批量导入</h5>
                                </div>
                                <div class="card-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">导入年度</label>
                                        <select class="form-select" id="importYear"></select>
                                    </div>
                                    <div class="upload-zone" id="uploadZone" onclick="document.getElementById('importFile').click()">
                                        <i class="bi bi-cloud-arrow-up d-block mb-3"></i>
                                        <p class="mb-1 fw-bold">点击或拖拽文件到此处</p>
                                        <p class="text-muted small mb-0">支持 CSV 格式（部门名称,预算收入,预算支出,决算收入,决算支出）</p>
                                        <p class="text-muted small mb-0" id="fileName"></p>
                                    </div>
                                    <input type="file" id="importFile" accept=".csv" style="display: none;">
                                    <button type="button" class="btn btn-success mt-3 px-4" onclick="doImport()">
                                        <i class="bi bi-upload me-1"></i>开始导入
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0 text-gov-blue fw-bold">
                                <i class="bi bi-table me-2"></i>数据列表
                            </h5>
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select form-select-sm" id="filterYear" style="width: 120px;" onchange="loadData()"></select>
                                <div class="input-group" style="width: 200px;">
                                    <input type="text" class="form-control form-control-sm" id="searchKeyword" placeholder="搜索部门...">
                                    <button class="btn btn-gov-blue btn-sm" onclick="loadData()"><i class="bi bi-search"></i></button>
                                </div>
                                <button class="btn btn-outline-success btn-sm" onclick="downloadExport('csv')">
                                    <i class="bi bi-download me-1"></i>CSV
                                </button>
                                <button class="btn btn-outline-primary btn-sm" onclick="downloadExport('xls')">
                                    <i class="bi bi-download me-1"></i>Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 budget-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>年度</th>
                                            <th>部门名称</th>
                                            <th class="text-end">预算收入</th>
                                            <th class="text-end">预算支出</th>
                                            <th class="text-end">决算收入</th>
                                            <th class="text-end">决算支出</th>
                                            <th class="text-center">差额率</th>
                                            <th class="text-center">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dataBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #004d99 0%, #003366 100%); color: white;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>编辑记录</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="editId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">年度</label>
                            <input type="number" class="form-control" id="editYear" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">部门名称</label>
                            <input type="text" class="form-control" id="editDepartment" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">预算收入(万元)</label>
                            <input type="number" step="0.01" class="form-control" id="editBudgetIncome" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">预算支出(万元)</label>
                            <input type="number" step="0.01" class="form-control" id="editBudgetExpenditure" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">决算收入(万元)</label>
                            <input type="number" step="0.01" class="form-control" id="editFinalIncome" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">决算支出(万元)</label>
                            <input type="number" step="0.01" class="form-control" id="editFinalExpenditure" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveEdit()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentYear = new Date().getFullYear();

        function init() {
            loadYears();
        }

        function loadYears() {
            fetch('budget_api.php?action=years')
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        const years = data.data;
                        currentYear = years[0] || currentYear;
                        populateYearSelects(years);
                        loadData();
                    }
                });
        }

        function populateYearSelects(years) {
            const selects = ['addYear', 'importYear', 'filterYear'];
            selects.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.innerHTML = years.map(y => `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y} 年度</option>`).join('');
            });
        }

        function loadData() {
            const year = document.getElementById('filterYear').value || currentYear;
            const keyword = document.getElementById('searchKeyword').value.trim();

            fetch(`budget_api.php?action=list&year=${year}&keyword=${encodeURIComponent(keyword)}&page_size=100`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderTable(data.data.list);
                    }
                });
        }

        function renderTable(list) {
            const tbody = document.getElementById('dataBody');
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">暂无数据</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(row => {
                let diffHtml = '<span class="text-muted">-</span>';
                let rowClass = '';
                if (row.diff_rate !== null) {
                    let badgeClass = 'diff-normal';
                    if (row.diff_rate >= 20) { badgeClass = 'diff-alert'; rowClass = 'diff-danger-row'; }
                    else if (row.diff_rate >= 10) { badgeClass = 'diff-warn'; rowClass = 'diff-warning-row'; }
                    diffHtml = `<span class="diff-badge ${badgeClass}">${row.diff_rate}%</span>`;
                }

                return `
                    <tr class="${rowClass}">
                        <td>${row.id}</td>
                        <td>${row.year}</td>
                        <td class="fw-bold">${escapeHtml(row.department)}</td>
                        <td class="amount-cell">${formatAmount(row.budget_income)}</td>
                        <td class="amount-cell">${formatAmount(row.budget_expenditure)}</td>
                        <td class="amount-cell">${formatAmount(row.final_income)}</td>
                        <td class="amount-cell">${formatAmount(row.final_expenditure)}</td>
                        <td class="text-center">${diffHtml}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openEdit(${row.id}, '${escapeHtml(row.department)}', ${row.year}, ${row.budget_income}, ${row.budget_expenditure}, ${row.final_income}, ${row.final_expenditure})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(${row.id}, '${escapeHtml(row.department)}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        document.getElementById('addForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const data = {
                year: parseInt(document.getElementById('addYear').value),
                department: document.getElementById('addDepartment').value.trim(),
                budget_income: parseFloat(document.getElementById('addBudgetIncome').value),
                budget_expenditure: parseFloat(document.getElementById('addBudgetExpenditure').value),
                final_income: parseFloat(document.getElementById('addFinalIncome').value),
                final_expenditure: parseFloat(document.getElementById('addFinalExpenditure').value)
            };

            fetch('budget_api.php?action=add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.code === 200) {
                    Swal.fire({ title: '新增成功', icon: 'success', confirmButtonText: '确定' });
                    document.getElementById('addForm').reset();
                    loadData();
                } else {
                    Swal.fire({ title: '新增失败', text: result.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        });

        function openEdit(id, dept, year, bi, be, fi, fe) {
            document.getElementById('editId').value = id;
            document.getElementById('editYear').value = year;
            document.getElementById('editDepartment').value = dept;
            document.getElementById('editBudgetIncome').value = bi;
            document.getElementById('editBudgetExpenditure').value = be;
            document.getElementById('editFinalIncome').value = fi;
            document.getElementById('editFinalExpenditure').value = fe;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function saveEdit() {
            const id = parseInt(document.getElementById('editId').value);
            const data = {
                id: id,
                year: parseInt(document.getElementById('editYear').value),
                department: document.getElementById('editDepartment').value.trim(),
                budget_income: parseFloat(document.getElementById('editBudgetIncome').value),
                budget_expenditure: parseFloat(document.getElementById('editBudgetExpenditure').value),
                final_income: parseFloat(document.getElementById('editFinalIncome').value),
                final_expenditure: parseFloat(document.getElementById('editFinalExpenditure').value)
            };

            fetch('budget_api.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.code === 200) {
                    Swal.fire({ title: '修改成功', icon: 'success', confirmButtonText: '确定' });
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    loadData();
                } else {
                    Swal.fire({ title: '修改失败', text: result.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function deleteRecord(id, dept) {
            Swal.fire({
                title: '确认删除',
                text: `确定要删除"${dept}"的预决算记录吗？`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '删除',
                cancelButtonText: '取消'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('budget_api.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({ title: '删除成功', icon: 'success', confirmButtonText: '确定' });
                            loadData();
                        } else {
                            Swal.fire({ title: '删除失败', text: data.msg, icon: 'error', confirmButtonText: '确定' });
                        }
                    });
                }
            });
        }

        document.getElementById('importFile').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : '';
            document.getElementById('fileName').textContent = fileName;
        });

        const uploadZone = document.getElementById('uploadZone');
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        uploadZone.addEventListener('dragleave', function() {
            this.classList.remove('dragover');
        });
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('importFile').files = files;
                document.getElementById('fileName').textContent = files[0].name;
            }
        });

        function doImport() {
            const fileInput = document.getElementById('importFile');
            if (!fileInput.files[0]) {
                Swal.fire({ title: '请选择文件', icon: 'warning', confirmButtonText: '确定' });
                return;
            }

            const year = document.getElementById('importYear').value;
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('year', year);

            fetch('budget_api.php?action=import', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                if (result.code === 200) {
                    Swal.fire({
                        title: '导入完成',
                        text: result.msg,
                        icon: 'success',
                        confirmButtonText: '确定'
                    });
                    document.getElementById('importFile').value = '';
                    document.getElementById('fileName').textContent = '';
                    loadData();
                } else {
                    Swal.fire({ title: '导入失败', text: result.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function downloadExport(format) {
            const year = document.getElementById('filterYear').value || currentYear;
            window.open(`budget_api.php?action=export&year=${year}&format=${format}`, '_blank');
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

        document.getElementById('searchKeyword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') loadData();
        });

        init();
    </script>

</body>
</html>
