<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>招聘管理 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .position-table th {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 12px 14px;
            white-space: nowrap;
        }
        .position-table td {
            padding: 10px 14px;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }
        .position-table tbody tr:hover {
            background-color: #e6f0ff !important;
        }
        .status-tag {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            display: inline-block;
        }
        .status-active { background-color: #e8f5e9; color: #2e7d32; }
        .status-closed { background-color: #eceff1; color: #546e7a; }
        .countdown-tag {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .cd-active { background-color: #e8f5e9; color: #2e7d32; }
        .cd-warning { background-color: #fff3e0; color: #e65100; }
        .cd-urgent { background-color: #ffebee; color: #c62828; }
        .cd-expired { background-color: #eceff1; color: #546e7a; }
        .tab-active { color: var(--gov-blue-primary); border-bottom: 3px solid var(--gov-blue-primary); font-weight: 700; }
        .tab-inactive { color: #6c757d; border-bottom: 3px solid transparent; }
        .tab-btn { padding: 0.75rem 1.5rem; border: none; background: none; cursor: pointer; font-size: 1rem; transition: all 0.2s; }
        .tab-btn:hover { color: var(--gov-blue-primary); }
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
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-radar me-2"></i>舆情监测看板
                    </a>
                    <a href="weather_config.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-sun me-2"></i>气象数据源
                    </a>
                    <a href="recruit.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-briefcase me-2"></i>招聘管理
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">招聘管理</li>
                        </ol>
                    </nav>

                    <div class="d-flex gap-4 mb-4 border-bottom">
                        <button class="tab-btn tab-active" id="tabPositions" onclick="switchTab('positions')">
                            <i class="bi bi-briefcase me-1"></i>岗位管理
                        </button>
                        <button class="tab-btn tab-inactive" id="tabApplications" onclick="switchTab('applications')">
                            <i class="bi bi-people me-1"></i>报名记录
                        </button>
                    </div>

                    <div id="positionsPanel">
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0 text-gov-blue fw-bold">
                                    <i class="bi bi-list-check me-2"></i>岗位列表
                                </h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select form-select-sm" id="statusFilter" style="width: 130px;">
                                        <option value="-1">全部状态</option>
                                        <option value="1">招聘中</option>
                                        <option value="0">已关闭</option>
                                    </select>
                                    <div class="input-group" style="width: 200px;">
                                        <input type="text" class="form-control form-control-sm" id="posKeyword" placeholder="搜索岗位...">
                                        <button class="btn btn-gov-blue btn-sm" onclick="loadPositions()"><i class="bi bi-search"></i></button>
                                    </div>
                                    <button class="btn btn-gov-blue btn-sm" onclick="openAddModal()">
                                        <i class="bi bi-plus-lg me-1"></i>新增岗位
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 position-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>岗位名称</th>
                                                <th>招聘单位</th>
                                                <th>学历要求</th>
                                                <th>人数</th>
                                                <th>报名截止</th>
                                                <th>倒计时</th>
                                                <th>报名数</th>
                                                <th>状态</th>
                                                <th class="text-center">操作</th>
                                            </tr>
                                        </thead>
                                        <tbody id="posBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="text-center py-3" id="posPagination"></div>
                    </div>

                    <div id="applicationsPanel" style="display:none;">
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0 text-gov-blue fw-bold">
                                    <i class="bi bi-people me-2"></i>报名记录
                                </h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <select class="form-select form-select-sm" id="appPositionFilter" style="width: 200px;">
                                        <option value="0">全部岗位</option>
                                    </select>
                                    <button class="btn btn-outline-success btn-sm" onclick="exportApplications()">
                                        <i class="bi bi-download me-1"></i>导出CSV
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 position-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>姓名</th>
                                                <th>手机号</th>
                                                <th>邮箱</th>
                                                <th>报名岗位</th>
                                                <th>招聘单位</th>
                                                <th>简历</th>
                                                <th>报名时间</th>
                                            </tr>
                                        </thead>
                                        <tbody id="appBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="text-center py-3" id="appPagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="posModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #004d99 0%, #003366 100%); color: white;">
                    <h5 class="modal-title fw-bold" id="posModalTitle"><i class="bi bi-plus-circle me-2"></i>新增岗位</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="posEditId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">岗位名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="posTitle" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">招聘单位 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="posDepartment" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">学历要求 <span class="text-danger">*</span></label>
                            <select class="form-select" id="posEducation" required>
                                <option value="">请选择</option>
                                <option value="大专">大专</option>
                                <option value="本科">本科</option>
                                <option value="硕士">硕士</option>
                                <option value="博士">博士</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">招聘人数 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="posHeadcount" min="1" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">报名截止日期 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="posDeadline" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">岗位职责</label>
                            <textarea class="form-control" id="posResponsibility" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">任职要求</label>
                            <textarea class="form-control" id="posRequirement" rows="4"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">报名方式</label>
                            <textarea class="form-control" id="posApplyMethod" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">状态</label>
                            <select class="form-select" id="posStatus">
                                <option value="1">招聘中</option>
                                <option value="0">已关闭</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="savePosition()">
                        <i class="bi bi-check-lg me-1"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentTab = 'positions';
        let posPage = 1;
        let appPage = 1;
        const pageSize = 10;

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tabPositions').className = 'tab-btn ' + (tab === 'positions' ? 'tab-active' : 'tab-inactive');
            document.getElementById('tabApplications').className = 'tab-btn ' + (tab === 'applications' ? 'tab-active' : 'tab-inactive');
            document.getElementById('positionsPanel').style.display = tab === 'positions' ? '' : 'none';
            document.getElementById('applicationsPanel').style.display = tab === 'applications' ? '' : 'none';

            if (tab === 'positions') loadPositions();
            else loadApplications();
        }

        function loadPositions() {
            const status = document.getElementById('statusFilter').value;
            const keyword = document.getElementById('posKeyword').value.trim();

            fetch(`recruit_api.php?action=list&page=${posPage}&page_size=${pageSize}&status=${status}&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderPosTable(data.data.list);
                        renderPosPagination(data.data);
                    }
                });
        }

        function renderPosTable(list) {
            const tbody = document.getElementById('posBody');
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">暂无岗位数据</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(pos => {
                let cdClass = 'cd-active', cdText = `剩余${pos.remaining_days}天`;
                if (pos.remaining_days <= 0) { cdClass = 'cd-expired'; cdText = '已截止'; }
                else if (pos.remaining_days <= 3) { cdClass = 'cd-urgent'; cdText = `仅${pos.remaining_days}天`; }
                else if (pos.remaining_days <= 7) { cdClass = 'cd-warning'; cdText = `${pos.remaining_days}天`; }

                const statusHtml = pos.status == 1
                    ? '<span class="status-tag status-active">招聘中</span>'
                    : '<span class="status-tag status-closed">已关闭</span>';

                return `
                    <tr>
                        <td>${pos.id}</td>
                        <td class="fw-bold">${escapeHtml(pos.title)}</td>
                        <td>${escapeHtml(pos.department)}</td>
                        <td>${escapeHtml(pos.education)}</td>
                        <td>${pos.headcount}</td>
                        <td>${pos.deadline}</td>
                        <td><span class="countdown-tag ${cdClass}">${cdText}</span></td>
                        <td><span class="badge bg-primary">${pos.apply_count || 0}</span></td>
                        <td>${statusHtml}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal(${pos.id})" title="编辑">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePosition(${pos.id}, '${escapeHtml(pos.title)}')" title="删除">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderPosPagination(data) {
            const container = document.getElementById('posPagination');
            if (data.total_pages <= 1) { container.innerHTML = `<span class="text-muted small">共 ${data.total} 条</span>`; return; }
            let html = `<nav class="d-inline-flex align-items-center gap-3"><span class="text-muted small">共 ${data.total} 条</span><ul class="pagination mb-0">`;
            html += `<li class="page-item ${posPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="posPage=${posPage-1};loadPositions()">上一页</a></li>`;
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= posPage - 1 && i <= posPage + 1))
                    html += `<li class="page-item ${posPage===i?'active':''}"><a class="page-link" onclick="posPage=${i};loadPositions()">${i}</a></li>`;
                else if (i === posPage - 2 || i === posPage + 2)
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            html += `<li class="page-item ${posPage===data.total_pages?'disabled':''}"><a class="page-link" onclick="posPage=${posPage+1};loadPositions()">下一页</a></li></ul></nav>`;
            container.innerHTML = html;
        }

        function openAddModal() {
            document.getElementById('posEditId').value = '';
            document.getElementById('posModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>新增岗位';
            document.getElementById('posTitle').value = '';
            document.getElementById('posDepartment').value = '';
            document.getElementById('posEducation').value = '';
            document.getElementById('posHeadcount').value = 1;
            document.getElementById('posDeadline').value = '';
            document.getElementById('posResponsibility').value = '';
            document.getElementById('posRequirement').value = '';
            document.getElementById('posApplyMethod').value = '';
            document.getElementById('posStatus').value = '1';
            new bootstrap.Modal(document.getElementById('posModal')).show();
        }

        function openEditModal(id) {
            fetch(`recruit_api.php?action=list&page=1&page_size=1&keyword=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200 && data.data.list.length > 0) {
                        const pos = data.data.list[0];
                        document.getElementById('posEditId').value = pos.id;
                        document.getElementById('posModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>编辑岗位';
                        document.getElementById('posTitle').value = pos.title;
                        document.getElementById('posDepartment').value = pos.department;
                        document.getElementById('posEducation').value = pos.education;
                        document.getElementById('posHeadcount').value = pos.headcount;
                        document.getElementById('posDeadline').value = pos.deadline;
                        document.getElementById('posResponsibility').value = pos.responsibility || '';
                        document.getElementById('posRequirement').value = pos.requirement || '';
                        document.getElementById('posApplyMethod').value = pos.apply_method || '';
                        document.getElementById('posStatus').value = pos.status;
                        new bootstrap.Modal(document.getElementById('posModal')).show();
                    }
                });
        }

        function savePosition() {
            const id = document.getElementById('posEditId').value;
            const data = {
                title: document.getElementById('posTitle').value.trim(),
                department: document.getElementById('posDepartment').value.trim(),
                education: document.getElementById('posEducation').value,
                headcount: parseInt(document.getElementById('posHeadcount').value),
                deadline: document.getElementById('posDeadline').value,
                responsibility: document.getElementById('posResponsibility').value.trim(),
                requirement: document.getElementById('posRequirement').value.trim(),
                apply_method: document.getElementById('posApplyMethod').value.trim(),
                status: parseInt(document.getElementById('posStatus').value)
            };

            if (!data.title || !data.department || !data.education || !data.deadline) {
                Swal.fire({ title: '提示', text: '请填写必填字段', icon: 'warning', confirmButtonText: '确定' });
                return;
            }

            const action = id ? 'update' : 'add';
            if (id) data.id = parseInt(id);

            fetch(`recruit_api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.code === 200) {
                    Swal.fire({ title: '保存成功', icon: 'success', confirmButtonText: '确定' });
                    bootstrap.Modal.getInstance(document.getElementById('posModal')).hide();
                    loadPositions();
                } else {
                    Swal.fire({ title: '保存失败', text: result.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function deletePosition(id, title) {
            Swal.fire({
                title: '确认删除',
                text: `确定要删除岗位"${title}"吗？相关报名记录也将被删除。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: '删除',
                cancelButtonText: '取消'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('recruit_api.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({ title: '删除成功', icon: 'success', confirmButtonText: '确定' });
                            loadPositions();
                        } else {
                            Swal.fire({ title: '删除失败', text: data.msg, icon: 'error', confirmButtonText: '确定' });
                        }
                    });
                }
            });
        }

        function loadApplications() {
            const positionId = document.getElementById('appPositionFilter').value;

            fetch(`recruit_api.php?action=applications&position_id=${positionId}&page=${appPage}&page_size=${pageSize}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderAppTable(data.data.list);
                        renderAppPagination(data.data);
                        updatePositionFilter();
                    }
                });
        }

        function updatePositionFilter() {
            fetch('recruit_api.php?action=list&page=1&page_size=100&status=-1')
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        const sel = document.getElementById('appPositionFilter');
                        const current = sel.value;
                        sel.innerHTML = '<option value="0">全部岗位</option>' + data.data.list.map(p => `<option value="${p.id}" ${p.id == current ? 'selected' : ''}>${escapeHtml(p.title)}</option>`).join('');
                    }
                });
        }

        function renderAppTable(list) {
            const tbody = document.getElementById('appBody');
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">暂无报名记录</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(app => `
                <tr>
                    <td>${app.id}</td>
                    <td class="fw-bold">${escapeHtml(app.name)}</td>
                    <td>${escapeHtml(app.phone)}</td>
                    <td>${escapeHtml(app.email)}</td>
                    <td>${escapeHtml(app.position_title || '-')}</td>
                    <td>${escapeHtml(app.department || '-')}</td>
                    <td>${app.resume ? '<a href="../' + app.resume + '" target="_blank" class="text-primary"><i class="bi bi-file-earmark-pdf me-1"></i>查看</a>' : '<span class="text-muted">无</span>'}</td>
                    <td>${app.create_time}</td>
                </tr>
            `).join('');
        }

        function renderAppPagination(data) {
            const container = document.getElementById('appPagination');
            if (data.total_pages <= 1) { container.innerHTML = `<span class="text-muted small">共 ${data.total} 条</span>`; return; }
            let html = `<nav class="d-inline-flex align-items-center gap-3"><span class="text-muted small">共 ${data.total} 条</span><ul class="pagination mb-0">`;
            html += `<li class="page-item ${appPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="appPage=${appPage-1};loadApplications()">上一页</a></li>`;
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= appPage - 1 && i <= appPage + 1))
                    html += `<li class="page-item ${appPage===i?'active':''}"><a class="page-link" onclick="appPage=${i};loadApplications()">${i}</a></li>`;
                else if (i === appPage - 2 || i === appPage + 2)
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            html += `<li class="page-item ${appPage===data.total_pages?'disabled':''}"><a class="page-link" onclick="appPage=${appPage+1};loadApplications()">下一页</a></li></ul></nav>`;
            container.innerHTML = html;
        }

        function exportApplications() {
            const positionId = document.getElementById('appPositionFilter').value;
            window.open(`recruit_api.php?action=export_applications&position_id=${positionId}`, '_blank');
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s || '';
            return d.innerHTML;
        }

        document.getElementById('statusFilter').addEventListener('change', function() { posPage = 1; loadPositions(); });
        document.getElementById('posKeyword').addEventListener('keypress', function(e) { if (e.key === 'Enter') { posPage = 1; loadPositions(); } });
        document.getElementById('appPositionFilter').addEventListener('change', function() { appPage = 1; loadApplications(); });

        loadPositions();
    </script>

</body>
</html>
