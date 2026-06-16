<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>市长信箱 | GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .code-input-group { gap: 0.5rem; }
        .code-input-group input {
            width: 3rem;
            text-align: center;
            font-weight: bold;
            font-size: 1.25rem;
            letter-spacing: 0.25rem;
        }
        .verification-modal .modal-dialog { max-width: 450px; }
        .tab-content-public .card { transition: all 0.2s; }
        .tab-content-public .card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .my-message-row:hover { background-color: #f8f9fa; }
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
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link active" href="mayor_mailbox.php">市长信箱</a></li>
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
                    <div class="card-body p-0" style="background: linear-gradient(135deg, #8B0000 0%, #C41E3A 50%, #E74C3C 100%);">
                        <div class="p-5 text-white">
                            <h2 class="fw-bold mb-2"><i class="bi bi-buildings me-2"></i>市长信箱</h2>
                            <p class="opacity-90 mb-0">您的声音，市长在听。实名提交、限时回复，搭建政民沟通的桥梁。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-3 shadow-sm" id="mainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-2 fw-bold py-3" id="tab-submit" data-bs-toggle="pill" data-bs-target="#pane-submit" type="button">
                    <i class="bi bi-pencil-square me-2"></i>我要写信
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-2 fw-bold py-3" id="tab-public" data-bs-toggle="pill" data-bs-target="#pane-public" type="button">
                    <i class="bi bi-chat-square-text me-2"></i>公开回复
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-2 fw-bold py-3" id="tab-mine" data-bs-toggle="pill" data-bs-target="#pane-mine" type="button">
                    <i class="bi bi-person-lines-fill me-2"></i>我的留言
                </button>
            </li>
        </ul>

        <div class="tab-content" id="mainTabContent">

            <div class="tab-pane fade show active" id="pane-submit" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 py-4">
                        <h5 class="mb-0 border-start border-4 border-danger ps-3 text-gov-blue fw-bold">
                            <i class="bi bi-pencil-square me-1"></i>实名提交留言
                        </h5>
                        <div class="alert alert-info mt-3 mb-0 small" style="border-left: 4px solid #0dcaf0;">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>温馨提示：</strong>市长信箱实行实名制，需填写真实姓名、身份证号并通过手机短信验证，提交后将获得唯一受理编号，用于查询办理进度。
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form id="submitForm" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">真实姓名 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="请输入您的真实姓名" required maxlength="50">
                                        <div class="invalid-feedback">请输入真实姓名</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_card" class="form-label fw-bold">身份证号 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                        <input type="text" class="form-control font-monospace" id="id_card" name="id_card" placeholder="请输入18位身份证号" required maxlength="18">
                                        <div class="invalid-feedback">请输入有效的18位身份证号</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-bold">手机号码 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                        <input type="tel" class="form-control font-monospace" id="phone" name="phone" placeholder="请输入11位手机号" required maxlength="11">
                                        <div class="invalid-feedback">请输入有效的手机号</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="sms_code" class="form-label fw-bold">短信验证码 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                        <input type="text" class="form-control font-monospace" id="sms_code" name="sms_code" placeholder="请输入6位验证码" required maxlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="sendCodeBtn" style="min-width: 120px;">获取验证码</button>
                                        <div class="invalid-feedback">请输入短信验证码</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="title" class="form-label fw-bold">留言标题 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="form-control" id="title" name="title" placeholder="请简要概括您的诉求或建议" required maxlength="255">
                                        <div class="invalid-feedback">请输入留言标题</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="content" class="form-label fw-bold">留言内容 <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="6" placeholder="请详细描述您的问题、建议或诉求，我们将认真对待每一封来信..." required style="resize: vertical;"></textarea>
                                    <div class="invalid-feedback">请输入留言内容</div>
                                    <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>内容将经过敏感词过滤，系统会自动记录您的提交IP。</div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="privacy_check" required>
                                        <label class="form-check-label small text-muted" for="privacy_check">
                                            我已阅读并同意<a href="#" class="text-decoration-none">《隐私政策》</a>，承诺所填信息真实有效，同意相关部门对我的个人信息进行必要核实。
                                        </label>
                                        <div class="invalid-feedback">请阅读并同意隐私政策</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-danger w-100 py-3 fw-bold" id="submitBtn">
                                    <i class="bi bi-send-fill me-2"></i>提交留言
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-public" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 py-4">
                        <h5 class="mb-0 border-start border-4 border-success ps-3 text-gov-blue fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-chat-square-quote me-1"></i>公开回复留言</span>
                            <span class="badge bg-success fs-6" id="publicTotal">共 0 条</span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="publicKeyword" placeholder="搜索留言标题、内容或回复...">
                                <button class="btn btn-gov-blue" type="button" onclick="searchPublic()">搜索</button>
                            </div>
                        </div>
                        <div id="publicList" class="tab-content-public"></div>
                        <div class="text-center py-4" id="publicPagination"></div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pane-mine" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 py-4">
                        <h5 class="mb-0 border-start border-4 border-primary ps-3 text-gov-blue fw-bold">
                            <i class="bi bi-person-lines-fill me-1"></i>查询我的留言
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div id="mineLoginSection">
                            <div class="row g-3 mb-4">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">手机号码</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                        <input type="tel" class="form-control font-monospace" id="my_phone" placeholder="请输入留言时使用的手机号" maxlength="11">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold">短信验证码</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                        <input type="text" class="form-control font-monospace" id="my_sms_code" placeholder="6位验证码" maxlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="mySendCodeBtn" style="min-width: 120px;">获取验证码</button>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary w-100 py-2 fw-bold" onclick="queryMyMessages()">
                                        <i class="bi bi-search me-1"></i>查询
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="mineListSection" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted small"><i class="bi bi-person-check me-1"></i>已验证手机号：<span id="verifiedPhone" class="fw-bold text-gov-blue"></span></span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="resetMineLogin()">
                                    <i class="bi bi-arrow-left me-1"></i>切换账号
                                </button>
                            </div>
                            <span class="badge bg-secondary mb-3" id="mineTotal">共 0 条</span>
                            <div id="mineList" class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>受理编号</th>
                                            <th>标题</th>
                                            <th>审核状态</th>
                                            <th>回复状态</th>
                                            <th>提交时间</th>
                                            <th>回复时间</th>
                                            <th>操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mineTableBody"></tbody>
                                </table>
                            </div>
                            <div class="text-center py-4" id="minePagination"></div>
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
                    <h5 class="modal-title fw-bold" id="detailModalTitle">留言详情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
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
        let publicPage = 1;
        let publicPageSize = 5;
        let minePage = 1;
        let minePageSize = 10;
        let codeCountdown = 0;
        let myCodeCountdown = 0;
        let myVerifiedPhone = null;

        const auditStatusLabels = {
            0: { text: '待审核', cls: 'bg-warning-subtle text-warning-emphasis' },
            1: { text: '已通过', cls: 'bg-success-subtle text-success-emphasis' },
            2: { text: '已拒绝', cls: 'bg-danger-subtle text-danger-emphasis' }
        };
        const replyStatusLabels = {
            0: { text: '未回复', cls: 'bg-secondary-subtle text-secondary-emphasis' },
            1: { text: '已回复', cls: 'bg-success-subtle text-success-emphasis' }
        };

        function startCodeCountdown(btnId, countdownVar) {
            if (countdownVar === 'codeCountdown') {
                codeCountdown = 60;
                const btn = document.getElementById(btnId);
                btn.disabled = true;
                const timer = setInterval(() => {
                    codeCountdown--;
                    btn.textContent = `${codeCountdown}s 后重发`;
                    if (codeCountdown <= 0) {
                        clearInterval(timer);
                        btn.disabled = false;
                        btn.textContent = '获取验证码';
                    }
                }, 1000);
            } else {
                myCodeCountdown = 60;
                const btn = document.getElementById(btnId);
                btn.disabled = true;
                const timer = setInterval(() => {
                    myCodeCountdown--;
                    btn.textContent = `${myCodeCountdown}s 后重发`;
                    if (myCodeCountdown <= 0) {
                        clearInterval(timer);
                        btn.disabled = false;
                        btn.textContent = '获取验证码';
                    }
                }, 1000);
            }
        }

        document.getElementById('sendCodeBtn').addEventListener('click', function() {
            const phone = document.getElementById('phone').value.trim();
            if (!phone) {
                Swal.fire({ title: '提示', text: '请先输入手机号', icon: 'warning' });
                return;
            }
            if (!/^1[3-9]\d{9}$/.test(phone)) {
                Swal.fire({ title: '提示', text: '手机号格式不正确', icon: 'warning' });
                return;
            }
            if (codeCountdown > 0) return;

            const formData = new FormData();
            formData.append('phone', phone);

            fetch('mayor_mailbox_api.php?action=send_code', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    startCodeCountdown('sendCodeBtn', 'codeCountdown');
                    Swal.fire({
                        title: '验证码已发送',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">验证码已发送至 <strong class="text-gov-blue">${phone}</strong></p>
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted small d-block mb-1">测试环境验证码</span>
                                    <span class="fw-bold fs-2 text-danger font-monospace">${data.data.debug_code}</span>
                                </div>
                                <p class="mt-2 mb-0 small text-muted">5分钟内有效</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: '我知道了'
                    });
                } else {
                    Swal.fire({ title: '发送失败', text: data.msg, icon: 'error' });
                }
            });
        });

        document.getElementById('mySendCodeBtn').addEventListener('click', function() {
            const phone = document.getElementById('my_phone').value.trim();
            if (!phone) {
                Swal.fire({ title: '提示', text: '请先输入手机号', icon: 'warning' });
                return;
            }
            if (!/^1[3-9]\d{9}$/.test(phone)) {
                Swal.fire({ title: '提示', text: '手机号格式不正确', icon: 'warning' });
                return;
            }
            if (myCodeCountdown > 0) return;

            const formData = new FormData();
            formData.append('phone', phone);

            fetch('mayor_mailbox_api.php?action=send_code', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    startCodeCountdown('mySendCodeBtn', 'myCodeCountdown');
                    Swal.fire({
                        title: '验证码已发送',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">验证码已发送至 <strong class="text-gov-blue">${phone}</strong></p>
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted small d-block mb-1">测试环境验证码</span>
                                    <span class="fw-bold fs-2 text-danger font-monospace">${data.data.debug_code}</span>
                                </div>
                                <p class="mt-2 mb-0 small text-muted">5分钟内有效</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: '我知道了'
                    });
                } else {
                    Swal.fire({ title: '发送失败', text: data.msg, icon: 'error' });
                }
            });
        });

        document.getElementById('submitForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2 spinner-border spinner-border-sm"></i>提交中...';

            const formData = new FormData(form);

            fetch('mayor_mailbox_api.php?action=submit', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>提交留言';

                if (data.code === 200) {
                    Swal.fire({
                        title: '提交成功！',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">您的留言已提交成功，请妥善保存受理编号：</p>
                                <div class="p-3 bg-light rounded border text-center">
                                    <span class="text-muted small d-block mb-1">受理编号</span>
                                    <span class="fw-bold fs-3 text-danger font-monospace">${data.data.message_no}</span>
                                </div>
                                <p class="mt-3 mb-0 small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    我们将在 15 个工作日内对您的留言进行审核并回复，请通过"我的留言"查询办理进度。
                                </p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: '我知道了',
                        allowOutsideClick: false
                    }).then(() => {
                        form.reset();
                        form.classList.remove('was-validated');
                    });
                } else {
                    Swal.fire({ title: '提交失败', text: data.msg, icon: 'error' });
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>提交留言';
                Swal.fire({ title: '网络错误', text: '请检查网络连接后重试', icon: 'error' });
            });
        });

        function loadPublicList() {
            const keyword = document.getElementById('publicKeyword').value;

            fetch(`mayor_mailbox_api.php?action=public_list&page=${publicPage}&page_size=${publicPageSize}&keyword=${encodeURIComponent(keyword)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderPublicList(data.data.list);
                        renderPublicPagination(data.data);
                        document.getElementById('publicTotal').textContent = `共 ${data.data.total} 条`;
                    }
                });
        }

        function renderPublicList(list) {
            const container = document.getElementById('publicList');
            if (list.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-1 mb-3 d-block opacity-50"></i>
                        <p class="mb-0">暂无公开回复的留言</p>
                    </div>
                `;
                return;
            }
            container.innerHTML = list.map(item => `
                <div class="card border shadow-sm mb-3" onclick="showDetail(${item.id})" style="cursor:pointer;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-info-subtle text-info font-monospace">#${item.message_no}</span>
                            </div>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>回复时间：${item.reply_time}</small>
                        </div>
                        <h6 class="fw-bold text-gov-blue mb-2">${item.title}</h6>
                        <div class="d-flex align-items-center gap-3 small text-muted mb-3">
                            <span><i class="bi bi-person me-1"></i>${item.name}</span>
                            <span><i class="bi bi-calendar me-1"></i>提交：${item.create_time}</span>
                        </div>
                        <div class="border-start border-4 border-success bg-success bg-opacity-10 p-3 rounded-end">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-patch-check-fill text-success fs-5"></i>
                                <span class="fw-bold text-success">官方回复</span>
                            </div>
                            <p class="mb-0" style="white-space: pre-wrap; line-height: 1.8;">${item.reply_content}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderPublicPagination(data) {
            const container = document.getElementById('publicPagination');
            if (data.total_pages <= 1) {
                container.innerHTML = '';
                return;
            }
            let html = '<nav><ul class="pagination justify-content-center">';
            html += `<li class="page-item ${publicPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="goToPublicPage(${publicPage - 1})">上一页</a></li>`;
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= publicPage - 1 && i <= publicPage + 1)) {
                    html += `<li class="page-item ${publicPage === i ? 'active' : ''}"><a class="page-link" onclick="goToPublicPage(${i})">${i}</a></li>`;
                } else if (i === publicPage - 2 || i === publicPage + 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            html += `<li class="page-item ${publicPage === data.total_pages ? 'disabled' : ''}"><a class="page-link" onclick="goToPublicPage(${publicPage + 1})">下一页</a></li>`;
            html += '</ul></nav>';
            container.innerHTML = html;
        }

        function goToPublicPage(page) {
            publicPage = page;
            loadPublicList();
        }

        function searchPublic() {
            publicPage = 1;
            loadPublicList();
        }

        document.getElementById('publicKeyword').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchPublic();
        });

        function queryMyMessages() {
            const phone = document.getElementById('my_phone').value.trim();
            const sms_code = document.getElementById('my_sms_code').value.trim();

            if (!phone || !sms_code) {
                Swal.fire({ title: '提示', text: '请填写手机号和验证码', icon: 'warning' });
                return;
            }
            if (!/^1[3-9]\d{9}$/.test(phone)) {
                Swal.fire({ title: '提示', text: '手机号格式不正确', icon: 'warning' });
                return;
            }

            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('sms_code', sms_code);

            fetch('mayor_mailbox_api.php?action=my_list', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    myVerifiedPhone = phone;
                    document.getElementById('verifiedPhone').textContent = phone;
                    document.getElementById('mineLoginSection').style.display = 'none';
                    document.getElementById('mineListSection').style.display = 'block';
                    renderMineList(data.data.list);
                    renderMinePagination(data.data);
                    document.getElementById('mineTotal').textContent = `共 ${data.data.total} 条`;
                } else {
                    Swal.fire({ title: '查询失败', text: data.msg, icon: 'error' });
                }
            });
        }

        function renderMineList(list) {
            const tbody = document.getElementById('mineTableBody');
            if (list.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox me-2"></i>暂无留言记录</td></tr>`;
                return;
            }
            tbody.innerHTML = list.map(item => `
                <tr class="my-message-row">
                    <td class="font-monospace small">${item.message_no}</td>
                    <td class="fw-bold">${item.title}</td>
                    <td><span class="badge ${auditStatusLabels[item.audit_status].cls}">${auditStatusLabels[item.audit_status].text}</span></td>
                    <td><span class="badge ${replyStatusLabels[item.reply_status].cls}">${replyStatusLabels[item.reply_status].text}</span></td>
                    <td class="small text-muted">${item.create_time}</td>
                    <td class="small text-muted">${item.reply_time || '-'}</td>
                    <td><button class="btn btn-sm btn-outline-primary" onclick="showDetail(${item.id})"><i class="bi bi-eye me-1"></i>查看</button></td>
                </tr>
            `).join('');
        }

        function renderMinePagination(data) {
            const container = document.getElementById('minePagination');
            if (data.total_pages <= 1) {
                container.innerHTML = '';
                return;
            }
            let html = '<nav><ul class="pagination justify-content-center">';
            html += `<li class="page-item ${minePage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="goToMinePage(${minePage - 1})">上一页</a></li>';
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= minePage - 1 && i <= minePage + 1)) {
                    html += `<li class="page-item ${minePage === i ? 'active' : ''}"><a class="page-link" onclick="goToMinePage(${i})">${i}</a></li>';
                } else if (i === minePage - 2 || i === minePage + 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            html += `<li class="page-item ${minePage === data.total_pages ? 'disabled' : ''}"><a class="page-link" onclick="goToMinePage(${minePage + 1})">下一页</a></li>';
            html += '</ul></nav>';
            container.innerHTML = html;
        }

        function goToMinePage(page) {
            minePage = page;
            const formData = new FormData();
            formData.append('phone', myVerifiedPhone);
            formData.append('sms_code', '');
            fetch(`mayor_mailbox_api.php?action=my_list&page=${minePage}&page_size=${minePageSize}`, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        renderMineList(data.data.list);
                        renderMinePagination(data.data);
                    }
                });
        }

        function resetMineLogin() {
            myVerifiedPhone = null;
            document.getElementById('mineLoginSection').style.display = 'block';
            document.getElementById('mineListSection').style.display = 'none';
            document.getElementById('my_phone').value = '';
            document.getElementById('my_sms_code').value = '';
        }

        function showDetail(id) {
            fetch(`mayor_mailbox_api.php?action=detail&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        const item = data.data;
                        document.getElementById('detailModalTitle').textContent = `留言详情 - ${item.message_no}`;

                        let html = `
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">受理编号</label>
                                    <p class="mb-0 font-monospace fw-bold text-gov-blue">${item.message_no}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small mb-1">提交时间</label>
                                    <p class="mb-0">${item.create_time}</p>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1">提问人</label>
                                    <p class="mb-0">${item.name}</p>
                                </div>
                                ${item.phone ? `
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1">联系电话</label>
                                    <p class="mb-0 font-monospace">${item.phone}</p>
                                </div>
                                ` : ''}
                                ${item.is_public !== undefined ? `
                                <div class="col-md-4">
                                    <label class="form-label text-muted small mb-1">可见范围</label>
                                    <p class="mb-0">${item.is_public ? '<i class="bi bi-globe2 me-1"></i>公开' : '<i class="bi bi-lock me-1"></i>仅本人可见'}</p>
                                </div>
                                ` : ''}
                            </div>
                        `;

                        if (item.audit_status !== undefined) {
                            html += `
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small mb-1">审核状态</label>
                                        <p class="mb-0"><span class="badge ${auditStatusLabels[item.audit_status].cls} bg-opacity-10 p-2">${auditStatusLabels[item.audit_status].text}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small mb-1">回复状态</label>
                                        <p class="mb-0"><span class="badge ${replyStatusLabels[item.reply_status].cls bg-opacity-10 p-2">${replyStatusLabels[item.reply_status].text}</span></p>
                                    </div>
                                </div>
                            `;
                            if (item.audit_status == 2 && item.reject_reason) {
                                html += `
                                    <div class="mb-3">
                                        <label class="form-label text-danger small fw-bold">
                                            <i class="bi bi-x-circle me-1"></i>拒绝原因
                                        </label>
                                        <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded">${item.reject_reason}</div>
                                    </div>
                                `;
                            }
                        }

                        html += `
                            <div class="mb-3">
                                <label class="form-label fw-bold">留言标题</label>
                                <div class="p-3 bg-light rounded">${item.title}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">留言内容</label>
                                <div class="p-3 bg-light rounded" style="white-space: pre-wrap; line-height: 1.8;">${item.content}</div>
                            </div>
                        `;

                        if (item.reply_content) {
                            html += `
                                <div class="mb-0">
                                    <label class="form-label fw-bold text-success">
                                        <i class="bi bi-patch-check-fill me-1"></i>官方回复
                                        ${item.reply_admin ? `<small class="text-muted fw-normal">· ${item.reply_admin}</small>` : ''}
                                        ${item.reply_time ? `<small class="text-muted fw-normal float-end">${item.reply_time}</small>` : ''}
                                    </label>
                                    <div class="p-3 bg-success bg-opacity-10 border-start border-4 border-success rounded-end" style="white-space: pre-wrap; line-height: 1.8;">${item.reply_content}</div>
                                </div>
                            `;
                        }

                        document.getElementById('detailModalBody').innerHTML = html;
                        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                        modal.show();
                    } else {
                        Swal.fire({ title: '错误', text: data.msg, icon: 'error' });
                    }
                });
        }

        loadPublicList();
    </script>
</body>
</html>
