<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>敏感词管理 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
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
                    <a href="mail_keywords.php" class="list-group-item list-group-item-action active">
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
                            <li class="breadcrumb-item"><a href="mail.php" class="text-decoration-none text-muted">意见信箱</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">敏感词管理</li>
                        </ol>
                    </nav>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div>
                                        <p class="text-muted small mb-1 text-uppercase fw-bold">敏感词总数</p>
                                        <h3 class="mb-0 fw-bold text-gov-blue" id="statTotal">0</h3>
                                    </div>
                                    <div class="icon-shape bg-light-blue text-gov-blue rounded-circle p-3">
                                        <i class="bi bi-shield-exclamation fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm rounded-3 h-100">
                                <div class="card-body d-flex align-items-center p-4">
                                    <div class="me-4">
                                        <i class="bi bi-info-circle text-info display-6"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1">功能说明</h6>
                                        <p class="mb-0 text-muted small">系统会自动将留言内容中的敏感词替换为 <code class="bg-light px-1 rounded">*</code> 后再保存。请在此处维护敏感词词库，确保留言内容合规。</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="keywordSearch" placeholder="搜索敏感词...">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>重置
                                        </button>
                                        <button class="btn btn-gov-blue" onclick="showAddModal()">
                                            <i class="bi bi-plus-lg me-1"></i>添加敏感词
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="showBatchAddModal()">
                                            <i class="bi bi-collection me-1"></i>批量添加
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 100px;">ID</th>
                                            <th>敏感词</th>
                                            <th style="width: 200px;">创建时间</th>
                                            <th style="width: 120px;">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="wordList">
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

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>添加敏感词</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">敏感词</label>
                        <input type="text" class="form-control" id="wordInput" placeholder="请输入敏感词，如：赌博" maxlength="100">
                        <div class="form-text text-muted">支持中英文，长度不超过100个字符</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveWord()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="batchAddModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-collection me-2"></i>批量添加敏感词</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">敏感词列表</label>
                        <textarea class="form-control" id="batchWordInput" rows="8" placeholder="每行一个敏感词，例如：&#10;暴力&#10;色情&#10;赌博"></textarea>
                        <div class="form-text text-muted">每行输入一个敏感词，系统会自动过滤重复的内容</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-primary" onclick="batchSaveWords()">批量保存</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 20;

        function loadWords() {
            const keyword = document.getElementById('keywordSearch').value;

            fetch(`mail_api.php?action=sensitive_list&page=${currentPage}&page_size=${pageSize}&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderList(data.data.list);
                        renderPagination(data.data);
                        document.getElementById('statTotal').textContent = data.data.total;
                    }
                });
        }

        function renderList(words) {
            const container = document.getElementById('wordList');

            if (words.length === 0) {
                container.innerHTML = `
                    <tr>
                        <td colspan="4">
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                                <p class="mb-0">暂无敏感词数据</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            container.innerHTML = words.map(w => `
                <tr>
                    <td class="font-monospace text-muted">#${w.id}</td>
                    <td>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 fs-6 fw-bold">
                            <i class="bi bi-exclamation-triangle me-1"></i>${w.word}
                        </span>
                    </td>
                    <td class="text-muted small">${w.create_time}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteWord(${w.id}, '${w.word}')">
                            <i class="bi bi-trash me-1"></i>删除
                        </button>
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

        function goToPage(page) {
            currentPage = page;
            loadWords();
        }

        function resetFilters() {
            document.getElementById('keywordSearch').value = '';
            currentPage = 1;
            loadWords();
        }

        function showAddModal() {
            document.getElementById('wordInput').value = '';
            const modal = new bootstrap.Modal(document.getElementById('addModal'));
            modal.show();
            setTimeout(() => document.getElementById('wordInput').focus(), 500);
        }

        function showBatchAddModal() {
            document.getElementById('batchWordInput').value = '';
            const modal = new bootstrap.Modal(document.getElementById('batchAddModal'));
            modal.show();
            setTimeout(() => document.getElementById('batchWordInput').focus(), 500);
        }

        function saveWord() {
            const word = document.getElementById('wordInput').value.trim();

            if (!word) {
                Swal.fire({ title: '提示', text: '请输入敏感词', icon: 'warning' });
                return;
            }

            fetch('mail_api.php?action=sensitive_add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ word: word })
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    Swal.fire({ title: '添加成功', icon: 'success', timer: 1500, showConfirmButton: false })
                        .then(() => {
                            bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
                            loadWords();
                        });
                } else {
                    Swal.fire({ title: '添加失败', text: data.msg, icon: 'error' });
                }
            });
        }

        async function batchSaveWords() {
            const text = document.getElementById('batchWordInput').value.trim();

            if (!text) {
                Swal.fire({ title: '提示', text: '请输入敏感词', icon: 'warning' });
                return;
            }

            const words = [...new Set(text.split('\n').map(w => w.trim()).filter(w => w))];
            
            if (words.length === 0) {
                Swal.fire({ title: '提示', text: '未检测到有效的敏感词', icon: 'warning' });
                return;
            }

            let success = 0;
            let failed = 0;
            let failedWords = [];

            Swal.fire({
                title: '批量处理中...',
                html: `正在处理 ${words.length} 个敏感词...`,
                didOpen: () => { Swal.showLoading(); },
                allowOutsideClick: false
            });

            for (let i = 0; i < words.length; i++) {
                try {
                    const r = await fetch('mail_api.php?action=sensitive_add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ word: words[i] })
                    });
                    const data = await r.json();
                    if (data.code === 200) {
                        success++;
                    } else {
                        failed++;
                        failedWords.push(`${words[i]} (${data.msg})`);
                    }
                } catch (e) {
                    failed++;
                    failedWords.push(`${words[i]} (网络错误)`);
                }
            }

            Swal.close();
            
            let html = `
                <div class="text-start">
                    <p class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>成功添加：<strong class="text-success">${success}</strong> 个</p>
                    <p class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i>失败：<strong class="text-danger">${failed}</strong> 个</p>
            `;
            if (failedWords.length > 0) {
                html += `
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">失败详情：</small>
                        <div class="p-2 bg-light rounded small" style="max-height: 150px; overflow-y: auto;">
                            ${failedWords.map(w => `<div class="text-danger">${w}</div>`).join('')}
                        </div>
                    </div>
                `;
            }
            html += '</div>';

            Swal.fire({
                title: '批量添加完成',
                html: html,
                icon: success > 0 ? 'success' : 'error',
                confirmButtonText: '确定'
            }).then(() => {
                if (success > 0) {
                    bootstrap.Modal.getInstance(document.getElementById('batchAddModal')).hide();
                    loadWords();
                }
            });
        }

        function deleteWord(id, word) {
            Swal.fire({
                title: '确认删除',
                html: `确定要删除敏感词 <strong class="text-danger">"${word}"</strong> 吗？`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '删除',
                cancelButtonText: '取消',
                confirmButtonColor: '#dc3545'
            }).then(r => {
                if (r.isConfirmed) {
                    fetch('mail_api.php?action=sensitive_delete', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.code === 200) {
                            Swal.fire({ title: '已删除', icon: 'success', timer: 1500, showConfirmButton: false })
                                .then(() => loadWords());
                        } else {
                            Swal.fire({ title: '删除失败', text: data.msg, icon: 'error' });
                        }
                    });
                }
            });
        }

        document.getElementById('keywordSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                currentPage = 1;
                loadWords();
            }
        });

        loadWords();
    </script>

</body>
</html>
