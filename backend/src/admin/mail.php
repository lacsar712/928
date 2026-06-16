<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>意见信箱管理 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            display: inline-block;
        }
        .status-0 { background-color: #fff3cd; color: #856404; }
        .status-1 { background-color: #d1e7dd; color: #0f5132; }
        .status-2 { background-color: #f8d7da; color: #842029; }

        .public-badge {
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .public-yes { background-color: #cfe2ff; color: #084298; }
        .public-no { background-color: #e2e3e5; color: #41464b; }

        .nav-tabs .nav-link {
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: bold;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .nav-tabs .nav-link:hover {
            color: #004d99;
            border-bottom-color: #dee2e6;
        }
        .nav-tabs .nav-link.active {
            color: #004d99;
            background: transparent;
            border-bottom: 3px solid #004d99;
        }
        .tab-count {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 0.1rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
        .nav-link.active .tab-count {
            background: #004d99;
            color: white;
        }

        .mail-card {
            transition: all 0.2s;
            cursor: pointer;
        }
        .mail-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .mail-card.unread::before {
            content: '';
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 10px;
            height: 10px;
            background: #dc3545;
            border-radius: 50%;
        }

        .content-preview {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
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
                    <a href="budget.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-stack me-2"></i>预决算管理
                    </a>
                    <a href="mail.php" class="list-group-item list-group-item-action active">
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
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">意见信箱管理</li>
                        </ol>
                    </nav>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">待审核</p>
                                        <h3 class="mb-0 fw-bold text-warning" id="statPending">0</h3>
                                    </div>
                                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                                        <i class="bi bi-hourglass-split fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">已通过</p>
                                        <h3 class="mb-0 fw-bold text-success" id="statApproved">0</h3>
                                    </div>
                                    <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle p-3">
                                        <i class="bi bi-check-circle-fill fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">已拒绝</p>
                                        <h3 class="mb-0 fw-bold text-danger" id="statRejected">0</h3>
                                    </div>
                                    <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                                        <i class="bi bi-x-circle-fill fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">总留言</p>
                                        <h3 class="mb-0 fw-bold text-gov-blue" id="statTotal">0</h3>
                                    </div>
                                    <div class="icon-shape bg-light-blue text-gov-blue rounded-circle p-3">
                                        <i class="bi bi-envelope fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom-0 p-0">
                            <ul class="nav nav-tabs px-3" id="mailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-status="0" type="button">
                                        <i class="bi bi-hourglass-split me-1"></i>待审核
                                        <span class="tab-count" id="tabCount0">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-status="1" type="button">
                                        <i class="bi bi-check-circle me-1"></i>已通过
                                        <span class="tab-count" id="tabCount1">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-status="2" type="button">
                                        <i class="bi bi-x-circle me-1"></i>已拒绝
                                        <span class="tab-count" id="tabCount2">0</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center mb-4">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="keyword" placeholder="搜索编号、姓名、邮箱、主题...">
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                                        </button>
                                        <button class="btn btn-gov-blue flex-grow-1 flex-md-grow-0" onclick="loadList()">
                                            <i class="bi bi-funnel me-1"></i>筛选
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-secondary" id="totalCount">共 0 条</span>
                            </div>

                            <div id="mailList">
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="detailTitle">留言详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                </div>
                <div class="modal-footer flex-column border-top">
                    <div class="w-100 mb-3" id="replySection">
                        <label class="form-label fw-bold"><i class="bi bi-reply-fill me-1"></i>官方回复</label>
                        <textarea class="form-control" id="replyContent" rows="4" placeholder="请输入官方回复内容..." style="resize: vertical;"></textarea>
                    </div>
                    <div class="d-flex gap-2 w-100 justify-content-end" id="actionButtons">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 10;
        let currentStatus = 0;
        let currentId = null;
        let detailData = null;

        const statusLabels = {
            0: { text: '待审核', cls: 'status-0' },
            1: { text: '已通过', cls: 'status-1' },
            2: { text: '已拒绝', cls: 'status-2' }
        };

        document.querySelectorAll('#mailTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('#mailTabs .nav-link').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentStatus = parseInt(this.dataset.status);
                currentPage = 1;
                loadList();
            });
        });

        function loadStats() {
            fetch('mail_api.php?action=stats')
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        document.getElementById('statPending').textContent = data.data.pending;
                        document.getElementById('statApproved').textContent = data.data.approved;
                        document.getElementById('statRejected').textContent = data.data.rejected;
                        document.getElementById('statTotal').textContent = data.data.total;
                        document.getElementById('tabCount0').textContent = data.data.pending;
                        document.getElementById('tabCount1').textContent = data.data.approved;
                        document.getElementById('tabCount2').textContent = data.data.rejected;
                    }
                });
        }

        function loadList() {
            const keyword = document.getElementById('keyword').value;

            fetch(`mail_api.php?action=list&page=${currentPage}&page_size=${pageSize}&status=${currentStatus}&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderList(data.data.list);
                        renderPagination(data.data);
                        document.getElementById('totalCount').textContent = `共 ${data.data.total} 条`;
                    }
                });
        }

        function renderList(list) {
            const container = document.getElementById('mailList');

            if (list.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                        <p class="mb-0">暂无${statusLabels[currentStatus].text}的留言</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = list.map(item => `
                <div class="card border shadow-sm mb-3 mail-card position-relative ${currentStatus === 0 ? 'unread' : ''}" onclick="showDetail(${item.id})">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="status-badge ${statusLabels[item.status].cls}">${statusLabels[item.status].text}</span>
                                <span class="public-badge ${item.is_public ? 'public-yes' : 'public-no'}">
                                    ${item.is_public ? '<i class="bi bi-globe2 me-1"></i>公开' : '<i class="bi bi-lock me-1"></i>不公开'}
                                </span>
                                ${item.reply_content ? '<span class="public-badge public-yes"><i class="bi bi-check2-circle me-1"></i>已回复</span>' : '<span class="public-badge public-no"><i class="bi bi-dash-circle me-1"></i>未回复</span>'}
                            </div>
                            <span class="badge bg-info-subtle text-info font-monospace">#${item.message_no}</span>
                        </div>
                        <h6 class="fw-bold text-gov-blue mb-2">${item.subject}</h6>
                        <p class="text-muted mb-3 content-preview small">${item.content}</p>
                        <div class="d-flex align-items-center gap-3 small text-muted">
                            <span><i class="bi bi-person me-1"></i>${item.name}</span>
                            <span><i class="bi bi-envelope me-1"></i>${item.email}</span>
                            <span class="ms-auto"><i class="bi bi-clock me-1"></i>${item.create_time}</span>
                        </div>
                    </div>
                </div>
            `).join('');
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
            loadList();
        }

        function resetFilters() {
            document.getElementById('keyword').value = '';
            currentPage = 1;
            loadList();
        }

        function showDetail(id) {
            currentId = id;

            fetch(`mail_api.php?action=detail&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        detailData = data.data;
                        const item = data.data;

                        document.getElementById('detailTitle').textContent = `留言详情 - ${item.message_no}`;

                        document.getElementById('detailContent').innerHTML = `
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">留言编号</label>
                                    <p class="mb-0 font-monospace fw-bold text-gov-blue">${item.message_no}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">状态</label>
                                    <p class="mb-0"><span class="status-badge ${statusLabels[item.status].cls}">${statusLabels[item.status].text}</span></p>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1">姓名</label>
                                    <p class="mb-0">${item.name}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1">邮箱</label>
                                    <p class="mb-0">${item.email}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1">是否公开</label>
                                    <p class="mb-0"><span class="public-badge ${item.is_public ? 'public-yes' : 'public-no'}">${item.is_public ? '公开' : '不公开'}</span></p>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">提交IP</label>
                                    <p class="mb-0 font-monospace small">${item.ip_address || '-'}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">提交时间</label>
                                    <p class="mb-0">${item.create_time}</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">主题</label>
                                <div class="p-3 bg-light rounded">${item.subject}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">留言内容</label>
                                <div class="p-3 bg-light rounded" style="white-space: pre-wrap; line-height: 1.8;">${item.content}</div>
                            </div>
                            ${item.reply_content ? `
                                <div class="mb-0">
                                    <label class="form-label fw-bold text-success">
                                        <i class="bi bi-patch-check-fill me-1"></i>官方回复
                                        ${item.reply_admin ? `<small class="text-muted fw-normal">· ${item.reply_admin}</small>` : ''}
                                        ${item.reply_time ? `<small class="text-muted fw-normal float-end">${item.reply_time}</small>` : ''}
                                    </label>
                                    <div class="p-3 bg-success bg-opacity-10 border-start border-4 border-success rounded-end" style="white-space: pre-wrap; line-height: 1.8;">${item.reply_content}</div>
                                </div>
                            ` : ''}
                        `;

                        const btnContainer = document.getElementById('actionButtons');
                        const replySection = document.getElementById('replySection');
                        const status = item.status;

                        if (status === 2) {
                            replySection.innerHTML = `
                                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis mb-0">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">该留言已被拒绝</h6>
                                            <p class="mb-0 small">
                                                已拒绝状态下的留言<strong>不会在前台公开列表展示</strong>，市民也收不到回复。
                                                如需回复，请先点击下方"退回待审"将留言恢复为待审状态，
                                                审核通过后再进行回复。
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            replySection.innerHTML = `
                                <label class="form-label fw-bold"><i class="bi bi-reply-fill me-1"></i>官方回复</label>
                                <textarea class="form-control" id="replyContent" rows="4" placeholder="请输入官方回复内容..." style="resize: vertical;"></textarea>
                            `;
                            document.getElementById('replyContent').value = item.reply_content || '';
                        }

                        let buttons = [];
                        buttons.push('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>');

                        if (status === 0) {
                            buttons.push(`<button type="button" class="btn btn-danger" onclick="auditAction(2)"><i class="bi bi-x-circle me-1"></i>拒绝</button>`);
                            buttons.push(`<button type="button" class="btn btn-success" onclick="auditAction(1)"><i class="bi bi-check-circle me-1"></i>通过</button>`);
                        } else if (status === 1) {
                            buttons.push(`<button type="button" class="btn btn-warning" onclick="auditAction(0)"><i class="bi bi-arrow-counterclockwise me-1"></i>退回待审</button>`);
                        } else if (status === 2) {
                            buttons.push(`<button type="button" class="btn btn-info text-white" onclick="auditAction(0)"><i class="bi bi-arrow-counterclockwise me-1"></i>退回待审</button>`);
                        }
                        
                        if (status !== 2) {
                            buttons.push(`<button type="button" class="btn btn-primary" onclick="saveReply()"><i class="bi bi-send me-1"></i>保存回复${status === 0 ? '并通过' : ''}</button>`);
                        }

                        btnContainer.innerHTML = buttons.join('');

                        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                        modal.show();
                    } else {
                        Swal.fire({ title: '错误', text: data.msg, icon: 'error' });
                    }
                });
        }

        function auditAction(newStatus) {
            const statusText = statusLabels[newStatus].text;
            
            Swal.fire({
                title: '确认操作',
                text: `确定要将此留言标记为"${statusText}"吗？`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '确定',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    fetch('mail_api.php?action=audit', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: currentId, status: newStatus })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({ title: '操作成功', icon: 'success', timer: 1500, showConfirmButton: false })
                                .then(() => {
                                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                                    loadList();
                                    loadStats();
                                });
                        } else {
                            Swal.fire({ title: '操作失败', text: data.msg, icon: 'error' });
                        }
                    });
                }
            });
        }

        function saveReply() {
            const replyContent = document.getElementById('replyContent').value.trim();
            
            if (!replyContent) {
                Swal.fire({ title: '提示', text: '请输入回复内容', icon: 'warning' });
                return;
            }

            const autoApprove = detailData.status === 0;
            const actionText = autoApprove ? '保存回复并通过审核' : '保存回复';

            Swal.fire({
                title: '确认操作',
                text: `确定要${actionText}吗？`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '确定',
                cancelButtonText: '取消'
            }).then(r => {
                if (r.isConfirmed) {
                    const body = {
                        id: currentId,
                        reply_content: replyContent
                    };
                    if (autoApprove) {
                        body.status = 1;
                    }

                    fetch('mail_api.php?action=reply', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({ title: '回复成功', icon: 'success', timer: 1500, showConfirmButton: false })
                                .then(() => {
                                    bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                                    loadList();
                                    loadStats();
                                });
                        } else {
                            Swal.fire({ title: '操作失败', text: data.msg, icon: 'error' });
                        }
                    });
                }
            });
        }

        document.getElementById('keyword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadList();
            }
        });

        loadStats();
        loadList();
    </script>

</body>
</html>
