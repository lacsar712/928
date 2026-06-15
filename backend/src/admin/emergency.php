<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>应急事件管理 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .severity-tag {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            color: white;
            display: inline-block;
        }
        .severity-1 { background-color: #dc3545; }
        .severity-2 { background-color: #fd7e14; }
        .severity-3 { background-color: #ffc107; color: #000; }
        .severity-4 { background-color: #0d6efd; }

        .status-tag {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            display: inline-block;
        }
        .status-1 { background-color: #e3f2fd; color: #1565c0; }
        .status-2 { background-color: #fff3e0; color: #e65100; }
        .status-3 { background-color: #e8f5e9; color: #2e7d32; }
        .status-4 { background-color: #eceff1; color: #546e7a; }

        .event-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .event-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .event-card.severity-1 { border-left-color: #dc3545; }
        .event-card.severity-2 { border-left-color: #fd7e14; }
        .event-card.severity-3 { border-left-color: #ffc107; }
        .event-card.severity-4 { border-left-color: #0d6efd; }

        .image-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .image-thumb:hover {
            transform: scale(1.1);
        }

        .filter-btn.active {
            background-color: #004d99;
            color: white;
            border-color: #004d99;
        }
    </style>
</head>
<body>
    
    <!-- Top Navbar -->
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
            <!-- Sidebar -->
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                    <a href="emergency.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>应急事件
                    </a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-upload me-2"></i>政策文件上传
                    </a>
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-radar me-2"></i>舆情监测看板
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">应急事件管理</li>
                        </ol>
                    </nav>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">待处理</p>
                                        <h3 class="mb-0 fw-bold text-danger" id="statPending">0</h3>
                                    </div>
                                    <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                                        <i class="bi bi-hourglass-split fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">已派单</p>
                                        <h3 class="mb-0 fw-bold text-warning" id="statDispatched">0</h3>
                                    </div>
                                    <div class="icon-shape bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                                        <i class="bi bi-truck fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">已处置</p>
                                        <h3 class="mb-0 fw-bold text-success" id="statResolved">0</h3>
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
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">已归档</p>
                                        <h3 class="mb-0 fw-bold text-secondary" id="statArchived">0</h3>
                                    </div>
                                    <div class="icon-shape bg-secondary bg-opacity-10 text-secondary rounded-circle p-3">
                                        <i class="bi bi-archive-fill fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="keyword" placeholder="搜索事件编号、地点、描述...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="severityFilter">
                                        <option value="0">全部等级</option>
                                        <option value="1">Ⅰ级（特别重大）</option>
                                        <option value="2">Ⅱ级（重大）</option>
                                        <option value="3">Ⅲ级（较大）</option>
                                        <option value="4">Ⅳ级（一般）</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="statusFilter">
                                        <option value="0">全部状态</option>
                                        <option value="1">待处理</option>
                                        <option value="2">已派单</option>
                                        <option value="3">已处置</option>
                                        <option value="4">已归档</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-gov-blue flex-grow-1" onclick="loadEvents()">
                                            <i class="bi bi-funnel me-1"></i>筛选
                                        </button>
                                        <button class="btn btn-outline-secondary flex-grow-1" onclick="resetFilters()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event List -->
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-gov-blue fw-bold">
                                <i class="bi bi-list-check me-2"></i>事件列表
                            </h5>
                            <span class="badge bg-secondary" id="totalCount">共 0 条</span>
                        </div>
                        <div class="card-body p-0">
                            <div id="eventList" class="p-4">
                                <!-- Events will be loaded here -->
                            </div>
                            <div class="text-center py-4" id="pagination">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="detailTitle">事件详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <!-- Detail content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
                    <div class="btn-group" id="statusButtons">
                        <!-- Status buttons will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <img id="previewImage" src="" class="w-100 rounded">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 10;
        let currentEventId = null;
        let currentEventStatus = null;

        const severityLabels = {
            1: { text: 'Ⅰ级', sub: '特别重大', cls: 'severity-1' },
            2: { text: 'Ⅱ级', sub: '重大', cls: 'severity-2' },
            3: { text: 'Ⅲ级', sub: '较大', cls: 'severity-3' },
            4: { text: 'Ⅳ级', sub: '一般', cls: 'severity-4' }
        };

        const statusLabels = {
            1: { text: '待处理', cls: 'status-1', next: 2, nextText: '派单' },
            2: { text: '已派单', cls: 'status-2', next: 3, nextText: '处置' },
            3: { text: '已处置', cls: 'status-3', next: 4, nextText: '归档' },
            4: { text: '已归档', cls: 'status-4', next: null, nextText: null }
        };

        const typeIcons = {
            '自然灾害': 'bi-cloud-lightning-rain text-primary',
            '事故灾难': 'bi-fire text-danger',
            '公共卫生': 'bi-heart-pulse text-success',
            '社会安全': 'bi-shield-exclamation text-warning'
        };

        function loadEvents() {
            const severity = document.getElementById('severityFilter').value;
            const status = document.getElementById('statusFilter').value;
            const keyword = document.getElementById('keyword').value;

            fetch(`emergency_api.php?action=list&page=${currentPage}&page_size=${pageSize}&severity=${severity}&status=${status}&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 200) {
                        renderEventList(data.data.list);
                        renderPagination(data.data);
                        updateStats(data.data.list);
                        document.getElementById('totalCount').textContent = `共 ${data.data.total} 条`;
                    }
                });
        }

        function renderEventList(events) {
            const container = document.getElementById('eventList');
            
            if (events.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                        <p class="mb-0">暂无事件数据</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = events.map(event => `
                <div class="card event-card ${severityLabels[event.severity].cls} border-0 shadow-sm mb-3" onclick="showDetail(${event.id})">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <i class="bi ${typeIcons[event.event_type]} display-4"></i>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="severity-tag ${severityLabels[event.severity].cls}">${severityLabels[event.severity].text} ${severityLabels[event.severity].sub}</span>
                                    <span class="status-tag ${statusLabels[event.status].cls}">${statusLabels[event.status].text}</span>
                                </div>
                                <h6 class="fw-bold mb-1">${event.event_type}</h6>
                                <p class="text-muted mb-0 small">
                                    <i class="bi bi-geo-alt me-1"></i>${event.location}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1 small"><i class="bi bi-calendar3 me-1"></i>${event.occur_time}</p>
                                <p class="mb-1 small"><i class="bi bi-clock me-1"></i>上报：${event.create_time}</p>
                                <p class="mb-0 small">
                                    <i class="bi bi-person me-1"></i>
                                    ${event.is_anonymous ? '<em>匿名上报</em>' : event.reporter_name}
                                    ${event.reporter_phone ? ` (${event.reporter_phone})` : ''}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2 align-items-center">
                                    ${event.images && event.images.length > 0 ? `
                                        <div class="d-flex">
                                            ${event.images.slice(0, 3).map(img => `<img src="../${img}" class="image-thumb me-1" onclick="event.stopPropagation(); showImage('../${img}')">`).join('')}
                                        </div>
                                    ` : '<span class="text-muted small">无图片</span>'}
                                    <div class="ms-auto">
                                        <span class="text-muted small">#${event.event_no}</span>
                                    </div>
                                </div>
                            </div>
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

        function updateStats(events) {
            const stats = { 1: 0, 2: 0, 3: 0, 4: 0 };
            events.forEach(e => stats[e.status]++);
            document.getElementById('statPending').textContent = stats[1];
            document.getElementById('statDispatched').textContent = stats[2];
            document.getElementById('statResolved').textContent = stats[3];
            document.getElementById('statArchived').textContent = stats[4];
        }

        function goToPage(page) {
            currentPage = page;
            loadEvents();
        }

        function resetFilters() {
            document.getElementById('keyword').value = '';
            document.getElementById('severityFilter').value = 0;
            document.getElementById('statusFilter').value = 0;
            currentPage = 1;
            loadEvents();
        }

        function showDetail(id) {
            currentEventId = id;
            
            fetch(`emergency_api.php?action=list&page=1&page_size=1&keyword=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 200 && data.data.list.length > 0) {
                        const event = data.data.list[0];
                        currentEventStatus = event.status;
                        
                        document.getElementById('detailTitle').textContent = `${event.event_type} - ${event.event_no}`;
                        
                        let imagesHtml = '';
                        if (event.images && event.images.length > 0) {
                            imagesHtml = `
                                <div class="mb-3">
                                    <label class="form-label fw-bold">现场图片</label>
                                    <div class="d-flex gap-2">
                                        ${event.images.map(img => `<img src="../${img}" class="image-thumb" onclick="showImage('../${img}')" style="width: 80px; height: 80px;">`).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        let coordHtml = '';
                        if (event.longitude && event.latitude) {
                            coordHtml = `
                                <div class="mb-3">
                                    <label class="form-label fw-bold">GPS坐标</label>
                                    <p class="mb-0 font-monospace">经度：${event.longitude} | 纬度：${event.latitude}</p>
                                </div>
                            `;
                        }

                        document.getElementById('detailContent').innerHTML = `
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">事件编号</label>
                                        <p class="mb-0 font-monospace text-gov-blue">${event.event_no}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">事件类型</label>
                                        <p class="mb-0"><i class="bi ${typeIcons[event.event_type]} me-1"></i>${event.event_type}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">严重等级</label>
                                        <p class="mb-0"><span class="severity-tag ${severityLabels[event.severity].cls}">${severityLabels[event.severity].text} ${severityLabels[event.severity].sub}</span></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">当前状态</label>
                                        <p class="mb-0"><span class="status-tag ${statusLabels[event.status].cls}">${statusLabels[event.status].text}</span></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">发生时间</label>
                                        <p class="mb-0">${event.occur_time}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">发生地点</label>
                                        <p class="mb-0"><i class="bi bi-geo-alt me-1"></i>${event.location}</p>
                                    </div>
                                    ${coordHtml}
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">上报时间</label>
                                        <p class="mb-0">${event.create_time}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">现场描述</label>
                                <div class="p-3 bg-light rounded">${event.description}</div>
                            </div>
                            ${imagesHtml}
                            <div class="mb-3">
                                <label class="form-label fw-bold">上报人信息</label>
                                <p class="mb-0">
                                    ${event.is_anonymous ? '<em class="text-muted">匿名上报</em>' : `<i class="bi bi-person me-1"></i>${event.reporter_name} <i class="bi bi-telephone ms-3 me-1"></i>${event.reporter_phone}`}
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">上报IP</label>
                                <p class="mb-0 font-monospace text-muted small">${event.ip_address}</p>
                            </div>
                        `;

                        const statusBtnContainer = document.getElementById('statusButtons');
                        const nextStatus = statusLabels[event.status].next;
                        if (nextStatus) {
                            let btnClass = '';
                            if (nextStatus === 2) btnClass = 'btn-warning';
                            else if (nextStatus === 3) btnClass = 'btn-success';
                            else if (nextStatus === 4) btnClass = 'btn-secondary';
                            
                            statusBtnContainer.innerHTML = `
                                <button type="button" class="btn ${btnClass} fw-bold" onclick="updateStatus(${nextStatus})">
                                    <i class="bi bi-arrow-right-circle me-1"></i>标记为"${statusLabels[nextStatus].text}"
                                </button>
                            `;
                        } else {
                            statusBtnContainer.innerHTML = `
                                <button type="button" class="btn btn-outline-secondary" disabled>
                                    <i class="bi bi-lock me-1"></i>已归档，无法变更
                                </button>
                            `;
                        }

                        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                        modal.show();
                    }
                });
        }

        function updateStatus(newStatus) {
            const statusText = statusLabels[newStatus].text;
            
            Swal.fire({
                title: '确认操作',
                text: `确定要将此事件标记为"${statusText}"吗？`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '确定',
                cancelButtonText: '取消'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('emergency_api.php?action=status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: currentEventId, status: newStatus })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({
                                title: '操作成功',
                                text: `事件已标记为"${statusText}"`,
                                icon: 'success',
                                confirmButtonText: '确定'
                            }).then(() => {
                                bootstrap.Modal.getInstance(document.getElementById('detailModal')).hide();
                                loadEvents();
                            });
                        } else {
                            Swal.fire({
                                title: '操作失败',
                                text: data.msg,
                                icon: 'error',
                                confirmButtonText: '确定'
                            });
                        }
                    });
                }
            });
        }

        function showImage(src) {
            document.getElementById('previewImage').src = src;
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }

        document.getElementById('keyword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadEvents();
            }
        });

        loadEvents();
    </script>

</body>
</html>
