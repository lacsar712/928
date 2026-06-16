<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>舆情关键词配置 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/opinion-dark.css" rel="stylesheet">
</head>
<body class="opinion-dark">

    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-radar me-2"></i>GovCore 舆情监测中心
            </a>
            <span class="navbar-text text-white">
                <span class="pulse-dot"></span>
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
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-display me-2"></i>监测大屏
                    </a>
                    <a href="opinion_keywords.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-tags-fill me-2"></i>关键词配置
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light grid-bg">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item"><a href="opinion_dashboard.php" class="text-decoration-none text-muted">舆情监测</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">关键词配置</li>
                        </ol>
                    </nav>

                    <div class="card tech-border mb-4">
                        <div class="card-body p-4">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="keywordSearch" placeholder="搜索关键词...">
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
                                <div class="col-md-7">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                                        </button>
                                        <button class="btn btn-primary" onclick="showAddModal()">
                                            <i class="bi bi-plus-lg me-1"></i>添加关键词
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="stat-card matched">
                                <div class="stat-label">正向关键词</div>
                                <div class="stat-value" id="statPositive">0</div>
                                <i class="bi bi-emoji-smile-fill stat-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">中性关键词</div>
                                <div class="stat-value" id="statNeutral">0</div>
                                <i class="bi bi-emoji-neutral-fill stat-icon"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card negative">
                                <div class="stat-label">负向关键词</div>
                                <div class="stat-value" id="statNegative">0</div>
                                <i class="bi bi-emoji-angry-fill stat-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-list-check me-2"></i>关键词列表
                            </h5>
                            <span class="badge bg-secondary" id="totalCount">共 0 条</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">ID</th>
                                            <th>关键词</th>
                                            <th style="width: 120px;">情感标签</th>
                                            <th style="width: 100px;">权重</th>
                                            <th style="width: 150px;">创建时间</th>
                                            <th style="width: 150px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="keywordList">
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

    <div class="modal fade" id="keywordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">添加关键词</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="keywordForm">
                        <input type="hidden" id="editId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">关键词</label>
                            <input type="text" class="form-control" id="keywordInput" placeholder="请输入关键词" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">情感标签</label>
                            <select class="form-select" id="sentimentSelect" required>
                                <option value="1">正向</option>
                                <option value="2">中性</option>
                                <option value="3">负向</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">权重 <small class="text-muted">(1-10)</small></label>
                            <input type="number" class="form-control" id="weightInput" min="1" max="10" value="1" required>
                            <div class="form-text text-muted">权重越高，匹配优先级越高</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveKeyword()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 20;
        let editingId = null;

        const sentimentLabels = {
            1: { text: '正向', cls: 'positive' },
            2: { text: '中性', cls: 'neutral' },
            3: { text: '负向', cls: 'negative' }
        };

        function loadKeywords() {
            const sentiment = document.getElementById('sentimentFilter').value;
            const keyword = document.getElementById('keywordSearch').value;

            fetch(`opinion_api.php?action=keyword_list&page=${currentPage}&page_size=${pageSize}&sentiment=${sentiment}&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.code === 200) {
                        renderKeywordList(data.data.list);
                        renderPagination(data.data);
                        updateStats(data.data.list);
                        document.getElementById('totalCount').textContent = `共 ${data.data.total} 条`;
                    }
                });
        }

        function renderKeywordList(keywords) {
            const container = document.getElementById('keywordList');

            if (keywords.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="6">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                                <p class="mb-0">暂无关键词数据</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            container.innerHTML = keywords.map(kw => `
                <tr>
                    <td class="font-monospace text-muted">#${kw.id}</td>
                    <td>
                        <span class="keyword-tag ${sentimentLabels[kw.sentiment].cls}">${kw.keyword}</span>
                    </td>
                    <td>
                        <span class="sentiment-tag sentiment-${sentimentLabels[kw.sentiment].cls}">
                            ${sentimentLabels[kw.sentiment].text}
                        </span>
                    </td>
                    <td>
                        <span class="weight-badge">${kw.weight}</span>
                    </td>
                    <td class="text-muted small">${kw.create_time}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="action-btn edit" onclick="showEditModal(${kw.id}, '${kw.keyword}', ${kw.sentiment}, ${kw.weight})">
                                <i class="bi bi-pencil me-1"></i>编辑
                            </button>
                            <button class="action-btn delete" onclick="deleteKeyword(${kw.id})">
                                <i class="bi bi-trash me-1"></i>删除
                            </button>
                        </div>
                    </td>
                </tr>
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

        function updateStats(keywords) {
            const stats = { 1: 0, 2: 0, 3: 0 };
            keywords.forEach(k => stats[k.sentiment]++);
            document.getElementById('statPositive').textContent = stats[1];
            document.getElementById('statNeutral').textContent = stats[2];
            document.getElementById('statNegative').textContent = stats[3];
        }

        function goToPage(page) {
            currentPage = page;
            loadKeywords();
        }

        function resetFilters() {
            document.getElementById('keywordSearch').value = '';
            document.getElementById('sentimentFilter').value = 0;
            currentPage = 1;
            loadKeywords();
        }

        function showAddModal() {
            editingId = null;
            document.getElementById('modalTitle').textContent = '添加关键词';
            document.getElementById('editId').value = '';
            document.getElementById('keywordInput').value = '';
            document.getElementById('sentimentSelect').value = '1';
            document.getElementById('weightInput').value = '1';

            const modal = new bootstrap.Modal(document.getElementById('keywordModal'));
            modal.show();
        }

        function showEditModal(id, keyword, sentiment, weight) {
            editingId = id;
            document.getElementById('modalTitle').textContent = '编辑关键词';
            document.getElementById('editId').value = id;
            document.getElementById('keywordInput').value = keyword;
            document.getElementById('sentimentSelect').value = sentiment;
            document.getElementById('weightInput').value = weight;

            const modal = new bootstrap.Modal(document.getElementById('keywordModal'));
            modal.show();
        }

        function saveKeyword() {
            const keyword = document.getElementById('keywordInput').value.trim();
            const sentiment = parseInt(document.getElementById('sentimentSelect').value);
            const weight = parseInt(document.getElementById('weightInput').value);

            if (!keyword) {
                Swal.fire({
                    title: '提示',
                    text: '请输入关键词',
                    icon: 'warning',
                    confirmButtonText: '确定'
                });
                return;
            }

            const action = editingId ? 'keyword_update' : 'keyword_add';
            const data = editingId
                ? { id: editingId, keyword, sentiment, weight }
                : { keyword, sentiment, weight };

            fetch(`opinion_api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.code === 200) {
                    Swal.fire({
                        title: '成功',
                        text: data.msg,
                        icon: 'success',
                        confirmButtonText: '确定'
                    }).then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('keywordModal')).hide();
                        loadKeywords();
                    });
                } else {
                    Swal.fire({
                        title: '失败',
                        text: data.msg,
                        icon: 'error',
                        confirmButtonText: '确定'
                    });
                }
            });
        }

        function deleteKeyword(id) {
            Swal.fire({
                title: '确认删除',
                text: '确定要删除这个关键词吗？此操作不可恢复。',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '删除',
                cancelButtonText: '取消',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('opinion_api.php?action=keyword_delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({
                                title: '已删除',
                                text: data.msg,
                                icon: 'success',
                                confirmButtonText: '确定'
                            }).then(() => {
                                loadKeywords();
                            });
                        } else {
                            Swal.fire({
                                title: '删除失败',
                                text: data.msg,
                                icon: 'error',
                                confirmButtonText: '确定'
                            });
                        }
                    });
                }
            });
        }

        document.getElementById('keywordSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadKeywords();
            }
        });

        document.getElementById('sentimentFilter').addEventListener('change', function() {
            currentPage = 1;
            loadKeywords();
        });

        loadKeywords();
    </script>

</body>
</html>
