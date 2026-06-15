<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>领导干部信息管理 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .leader-avatar {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e9ecef;
        }
        .dept-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .dept-市委 { background-color: #e3f2fd; color: #1565c0; }
        .dept-市政府 { background-color: #fff3e0; color: #e65100; }
        .dept-人大 { background-color: #e8f5e9; color: #2e7d32; }
        .dept-政协 { background-color: #f3e5f5; color: #6a1b9a; }
        .avatar-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            background: #f8f9fa;
        }
        .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px dashed #adb5bd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            font-size: 2.5rem;
            background: #f8f9fa;
        }
        .table-row-clickable { cursor: pointer; transition: background-color 0.2s; }
        .table-row-clickable:hover { background-color: #e3f2fd !important; }
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
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-upload me-2"></i>政策文件上传
                    </a>
                    <a href="meeting_rooms.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-door-open me-2"></i>会议室管理
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
                    <a href="leaders.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-people-fill me-2"></i>领导干部信息
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">控制台</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">领导干部信息管理</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <select class="form-select" id="deptFilter">
                                        <option value="">全部部门</option>
                                        <option value="市委">市委</option>
                                        <option value="市政府">市政府</option>
                                        <option value="人大">人大</option>
                                        <option value="政协">政协</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="keyword" placeholder="搜索姓名、职务、分管领域...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-gov-blue flex-grow-1" onclick="loadLeaders()">
                                            <i class="bi bi-funnel me-1"></i>筛选
                                        </button>
                                        <button class="btn btn-outline-secondary flex-grow-1" onclick="resetFilters()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                                        </button>
                                        <button class="btn btn-success flex-grow-1" onclick="openModal()">
                                            <i class="bi bi-plus-lg me-1"></i>新增干部
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">市委</p>
                                        <h3 class="mb-0 fw-bold" style="color: #1565c0;" id="statSw">0</h3>
                                    </div>
                                    <div class="icon-shape rounded-circle p-3" style="background-color: #e3f2fd; color: #1565c0;">
                                        <i class="bi bi-building fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">市政府</p>
                                        <h3 class="mb-0 fw-bold" style="color: #e65100;" id="statGov">0</h3>
                                    </div>
                                    <div class="icon-shape rounded-circle p-3" style="background-color: #fff3e0; color: #e65100;">
                                        <i class="bi bi-bank fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">人大</p>
                                        <h3 class="mb-0 fw-bold" style="color: #2e7d32;" id="statRd">0</h3>
                                    </div>
                                    <div class="icon-shape rounded-circle p-3" style="background-color: #e8f5e9; color: #2e7d32;">
                                        <i class="bi bi-chat-left-quote fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">政协</p>
                                        <h3 class="mb-0 fw-bold" style="color: #6a1b9a;" id="statZx">0</h3>
                                    </div>
                                    <div class="icon-shape rounded-circle p-3" style="background-color: #f3e5f5; color: #6a1b9a;">
                                        <i class="bi bi-chat-dots fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-gov-blue fw-bold">
                                <i class="bi bi-list-check me-2"></i>干部信息列表
                            </h5>
                            <span class="badge bg-secondary" id="totalCount">共 0 条</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="min-width: 900px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3" style="width: 80px;">头像</th>
                                            <th class="px-4 py-3">姓名</th>
                                            <th class="px-4 py-3">职务</th>
                                            <th class="px-4 py-3" style="width: 100px;">部门</th>
                                            <th class="px-4 py-3">分管领域</th>
                                            <th class="px-4 py-3">邮箱</th>
                                            <th class="px-4 py-3" style="width: 70px;">排序</th>
                                            <th class="px-4 py-3 text-center" style="width: 160px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="leaderTableBody">
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center py-4" id="pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="leaderModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">新增领导干部</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="leaderForm" onsubmit="return saveLeader(event)">
                    <div class="modal-body p-4">
                        <input type="hidden" id="leaderId">
                        <div class="row g-4">
                            <div class="col-md-4 text-center">
                                <div id="avatarContainer" class="mb-3">
                                    <div class="avatar-placeholder mx-auto mb-3"><i class="bi bi-person"></i></div>
                                </div>
                                <input type="hidden" id="avatarPath">
                                <input type="file" class="form-control form-control-sm" id="avatarFile" accept="image/*" onchange="uploadAvatar(this)">
                                <div class="form-text text-muted mt-2">支持 JPG/PNG/GIF 格式，不超过 5MB</div>
                            </div>
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">姓名 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="leaderName" required placeholder="请输入姓名">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">所属部门 <span class="text-danger">*</span></label>
                                        <select class="form-select" id="leaderDept" required>
                                            <option value="">请选择部门</option>
                                            <option value="市委">市委</option>
                                            <option value="市政府">市政府</option>
                                            <option value="人大">人大</option>
                                            <option value="政协">政协</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">职务 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="leaderPosition" required placeholder="请输入职务">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">邮箱</label>
                                        <input type="email" class="form-control" id="leaderEmail" placeholder="请输入邮箱">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">排序权重</label>
                                        <input type="number" class="form-control" id="leaderSort" value="0" min="0" max="999">
                                        <div class="form-text text-muted">数字越小越靠前</div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">分管领域</label>
                                        <textarea class="form-control" id="leaderResp" rows="2" placeholder="请输入分管领域"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">简介</label>
                                        <textarea class="form-control" id="leaderBio" rows="4" placeholder="请输入个人简介"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-gov-blue px-4">
                            <i class="bi bi-save me-1"></i><span id="submitBtnText">保存</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 10;
        let isEdit = false;

        function loadLeaders() {
            const dept = document.getElementById('deptFilter').value;
            const keyword = document.getElementById('keyword').value;

            fetch(`../leaders_api.php?action=list&page=${currentPage}&page_size=${pageSize}&department=${encodeURIComponent(dept)}&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderTable(data.data.list);
                        renderPagination(data.data);
                        updateStats(data.data.list);
                        document.getElementById('totalCount').textContent = `共 ${data.data.total} 条`;
                    }
                });
        }

        function renderTable(list) {
            const tbody = document.getElementById('leaderTableBody');
            if (list.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>暂无数据</td></tr>`;
                return;
            }
            tbody.innerHTML = list.map(l => `
                <tr class="table-row-clickable" ondblclick="viewLeader(${l.id})">
                    <td class="px-4 py-3">
                        ${l.avatar ? `<img src="../${l.avatar}" class="leader-avatar">` : `<div class="leader-avatar d-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-person fs-4"></i></div>`}
                    </td>
                    <td class="px-4 py-3 fw-bold">${escapeHtml(l.name)}</td>
                    <td class="px-4 py-3">${escapeHtml(l.position)}</td>
                    <td class="px-4 py-3"><span class="dept-badge dept-${l.department}">${l.department}</span></td>
                    <td class="px-4 py-3"><small class="text-muted">${escapeHtml(l.responsibility || '-')}</small></td>
                    <td class="px-4 py-3"><small>${escapeHtml(l.email || '-')}</small></td>
                    <td class="px-4 py-3 text-center"><span class="badge bg-light text-dark">${l.sort_order}</span></td>
                    <td class="px-4 py-3 text-center">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editLeader(${l.id})" title="编辑"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLeader(${l.id}, '${escapeHtml(l.name)}')" title="删除"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }

        function renderPagination(data) {
            const container = document.getElementById('pagination');
            if (data.total_pages <= 1) { container.innerHTML = ''; return; }
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

        function updateStats(list) {
            const stats = { '市委': 0, '市政府': 0, '人大': 0, '政协': 0 };
            list.forEach(l => { if (stats[l.department] !== undefined) stats[l.department]++; });
            document.getElementById('statSw').textContent = stats['市委'];
            document.getElementById('statGov').textContent = stats['市政府'];
            document.getElementById('statRd').textContent = stats['人大'];
            document.getElementById('statZx').textContent = stats['政协'];
        }

        function goToPage(p) { currentPage = p; loadLeaders(); }

        function resetFilters() {
            document.getElementById('deptFilter').value = '';
            document.getElementById('keyword').value = '';
            currentPage = 1;
            loadLeaders();
        }

        function openModal() {
            isEdit = false;
            document.getElementById('modalTitle').textContent = '新增领导干部';
            document.getElementById('submitBtnText').textContent = '保存';
            document.getElementById('leaderForm').reset();
            document.getElementById('leaderId').value = '';
            document.getElementById('avatarPath').value = '';
            document.getElementById('leaderSort').value = 0;
            document.getElementById('avatarContainer').innerHTML = '<div class="avatar-placeholder mx-auto mb-3"><i class="bi bi-person"></i></div>';
            new bootstrap.Modal(document.getElementById('leaderModal')).show();
        }

        function editLeader(id) {
            fetch(`../leaders_api.php?action=detail&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        const l = data.data;
                        isEdit = true;
                        document.getElementById('modalTitle').textContent = '编辑领导干部';
                        document.getElementById('submitBtnText').textContent = '更新';
                        document.getElementById('leaderId').value = l.id;
                        document.getElementById('leaderName').value = l.name;
                        document.getElementById('leaderDept').value = l.department;
                        document.getElementById('leaderPosition').value = l.position;
                        document.getElementById('leaderEmail').value = l.email || '';
                        document.getElementById('leaderSort').value = l.sort_order;
                        document.getElementById('leaderResp').value = l.responsibility || '';
                        document.getElementById('leaderBio').value = l.bio || '';
                        document.getElementById('avatarPath').value = l.avatar || '';
                        if (l.avatar) {
                            document.getElementById('avatarContainer').innerHTML = `<img src="../${l.avatar}" class="avatar-preview mx-auto mb-3 d-block">`;
                        } else {
                            document.getElementById('avatarContainer').innerHTML = '<div class="avatar-placeholder mx-auto mb-3"><i class="bi bi-person"></i></div>';
                        }
                        new bootstrap.Modal(document.getElementById('leaderModal')).show();
                    }
                });
        }

        function viewLeader(id) { editLeader(id); }

        function uploadAvatar(input) {
            const file = input.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('avatar', file);
            fetch('../leaders_api.php?action=upload', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        document.getElementById('avatarPath').value = data.data.path;
                        document.getElementById('avatarContainer').innerHTML = `<img src="../${data.data.path}" class="avatar-preview mx-auto mb-3 d-block">`;
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: '头像上传成功', showConfirmButton: false, timer: 2000 });
                    } else {
                        Swal.fire({ icon: 'error', title: '上传失败', text: data.msg });
                    }
                });
        }

        function saveLeader(e) {
            e.preventDefault();
            const fd = new FormData();
            if (isEdit) fd.append('id', document.getElementById('leaderId').value);
            fd.append('name', document.getElementById('leaderName').value);
            fd.append('department', document.getElementById('leaderDept').value);
            fd.append('position', document.getElementById('leaderPosition').value);
            fd.append('email', document.getElementById('leaderEmail').value);
            fd.append('sort_order', document.getElementById('leaderSort').value);
            fd.append('responsibility', document.getElementById('leaderResp').value);
            fd.append('bio', document.getElementById('leaderBio').value);
            fd.append('avatar', document.getElementById('avatarPath').value);

            const action = isEdit ? 'edit' : 'add';
            fetch(`../leaders_api.php?action=${action}`, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        Swal.fire({ icon: 'success', title: isEdit ? '修改成功' : '添加成功', confirmButtonText: '确定' })
                            .then(() => { bootstrap.Modal.getInstance(document.getElementById('leaderModal')).hide(); loadLeaders(); });
                    } else {
                        Swal.fire({ icon: 'error', title: '操作失败', text: data.msg });
                    }
                });
            return false;
        }

        function deleteLeader(id, name) {
            Swal.fire({
                title: '确认删除',
                html: `确定要删除领导干部 <strong>"${name}"</strong> 吗？<br><small class="text-muted">此操作不可恢复</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '确认删除',
                cancelButtonText: '取消',
                confirmButtonColor: '#dc3545'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('../leaders_api.php?action=delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    }).then(r => r.json()).then(data => {
                        if (data.code === 200) {
                            Swal.fire({ icon: 'success', title: '删除成功', timer: 1500, showConfirmButton: false })
                                .then(() => loadLeaders());
                        } else {
                            Swal.fire({ icon: 'error', title: '删除失败', text: data.msg });
                        }
                    });
                }
            });
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s || '';
            return d.innerHTML;
        }

        document.getElementById('keyword').addEventListener('keypress', e => { if (e.key === 'Enter') { currentPage = 1; loadLeaders(); } });

        loadLeaders();
    </script>
</body>
</html>
