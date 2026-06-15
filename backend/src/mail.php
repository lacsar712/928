<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>政民互动 - 公开意见信箱 | GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link active" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="budget.php">预决算公开</a></li>
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

    <div class="container my-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #4a90d9 100%);">
                        <div class="p-5 text-white">
                            <h2 class="fw-bold mb-2"><i class="bi bi-envelope-open-heart me-2"></i>政民互动 · 公开意见信箱</h2>
                            <p class="opacity-90 mb-0">倾听民声、汇聚民智、解决民忧。您的每一条建议都是我们前进的动力。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-3 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0 border-start border-4 border-primary ps-3 text-gov-blue fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>提交留言
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="mailForm" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">姓名 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="请输入您的姓名" required maxlength="50">
                                </div>
                                <div class="invalid-feedback">请输入您的姓名</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">邮箱 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="请输入您的邮箱" required>
                                </div>
                                <div class="invalid-feedback">请输入有效的邮箱地址</div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label fw-bold">主题 <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="请简要概括您的留言主题" required maxlength="255">
                                </div>
                                <div class="invalid-feedback">请输入留言主题</div>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label fw-bold">留言内容 <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="5" placeholder="请详细描述您的意见、建议或诉求..." required style="resize: vertical;"></textarea>
                                <div class="invalid-feedback">请输入留言内容</div>
                                <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>我们会对内容进行敏感词过滤，违规内容将被自动屏蔽。</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">是否公开</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" checked>
                                    <label class="form-check-label" for="is_public">
                                        <span class="fw-bold">公开此留言</span>
                                        <small class="text-muted d-block">公开后，审核通过的留言将在下方列表中展示，其他用户可看到您的留言及官方回复。</small>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-gov-blue w-100 py-2 fw-bold" id="submitBtn">
                                <i class="bi bi-send-fill me-2"></i>提交留言
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0 border-start border-4 border-success ps-3 text-gov-blue fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-chat-left-quote me-1"></i>公开留言 · 官方回复</span>
                            <span class="badge bg-success fs-6" id="totalCount">共 0 条</span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="listKeyword" placeholder="搜索公开留言主题或内容...">
                                <button class="btn btn-gov-blue" type="button" onclick="searchList()">搜索</button>
                            </div>
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

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentPage = 1;
        let pageSize = 5;

        document.getElementById('mailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = e.target;
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2 spinner-border spinner-border-sm"></i>提交中...';

            const formData = new FormData();
            formData.append('name', document.getElementById('name').value.trim());
            formData.append('email', document.getElementById('email').value.trim());
            formData.append('subject', document.getElementById('subject').value.trim());
            formData.append('content', document.getElementById('content').value.trim());
            formData.append('is_public', document.getElementById('is_public').checked ? 1 : 0);

            fetch('mail_api.php?action=submit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>提交留言';

                if (data.code === 200) {
                    Swal.fire({
                        title: '提交成功！',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">您的留言已提交成功，请妥善保存留言编号以便后续查询：</p>
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted small d-block mb-1">留言编号</span>
                                    <span class="fw-bold fs-4 text-gov-blue font-monospace">${data.data.message_no}</span>
                                </div>
                                <p class="mt-3 mb-0 small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    我们将在 3-5 个工作日内对您的留言进行审核并回复，请耐心等待。
                                </p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: '我知道了',
                        allowOutsideClick: false
                    }).then(() => {
                        form.reset();
                        form.classList.remove('was-validated');
                        document.getElementById('is_public').checked = true;
                        loadList();
                    });
                } else {
                    Swal.fire({
                        title: '提交失败',
                        text: data.msg,
                        icon: 'error',
                        confirmButtonText: '确定'
                    });
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>提交留言';
                Swal.fire({
                    title: '网络错误',
                    text: '请检查网络连接后重试',
                    icon: 'error',
                    confirmButtonText: '确定'
                });
            });
        });

        function loadList() {
            const keyword = document.getElementById('listKeyword').value;
            
            fetch(`mail_api.php?action=public_list&page=${currentPage}&page_size=${pageSize}&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
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
                        <p class="mb-0">暂无公开留言</p>
                        <p class="small mb-0 mt-2">成为第一个发表意见的人吧！</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = list.map(item => `
                <div class="card border shadow-sm mb-3 mail-item">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%; font-weight: bold;">
                                    ${item.name.charAt(0)}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">${item.name}</h6>
                                    <small class="text-muted"><i class="bi bi-clock me-1"></i>${item.create_time}</small>
                                </div>
                            </div>
                            <span class="badge bg-info-subtle text-info">#${item.message_no}</span>
                        </div>
                        <h6 class="fw-bold text-gov-blue mb-2">
                            <i class="bi bi-chat-dots me-1"></i>${item.subject}
                        </h6>
                        <p class="mb-3 text-dark" style="white-space: pre-wrap; line-height: 1.8;">${item.content}</p>
                        
                        ${item.reply_content ? `
                            <div class="border-start border-4 border-success bg-success bg-opacity-10 p-3 rounded-end">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-patch-check-fill text-success fs-5"></i>
                                    <span class="fw-bold text-success">官方回复</span>
                                    ${item.reply_time ? `<small class="text-muted">· ${item.reply_time}</small>` : ''}
                                </div>
                                <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">${item.reply_content}</p>
                            </div>
                        ` : `
                            <div class="border-start border-4 border-secondary bg-secondary bg-opacity-10 p-3 rounded-end">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-hourglass-split text-secondary"></i>
                                    <span class="text-secondary small fw-bold">正在处理中，请耐心等待...</span>
                                </div>
                            </div>
                        `}
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
            document.querySelector('.col-md-7').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function searchList() {
            currentPage = 1;
            loadList();
        }

        document.getElementById('listKeyword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchList();
            }
        });

        loadList();
    </script>
</body>
</html>
