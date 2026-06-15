<?php
require_once 'func.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: recruit.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>岗位详情 - 公开招聘 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .detail-section {
            background: white;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .detail-section h5 {
            color: var(--gov-blue-primary);
            font-weight: 700;
            border-left: 4px solid var(--gov-blue-primary);
            padding-left: 0.75rem;
            margin-bottom: 1rem;
        }
        .detail-section .content-text {
            white-space: pre-line;
            line-height: 1.8;
            color: var(--gov-text-main);
        }
        .info-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: #f8f9fa;
        }
        .info-item label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
            display: block;
        }
        .info-item span {
            font-weight: 600;
            color: var(--gov-text-main);
        }
        .countdown-big {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .countdown-active { background-color: #e8f5e9; color: #2e7d32; }
        .countdown-warning { background-color: #fff3e0; color: #e65100; }
        .countdown-urgent { background-color: #ffebee; color: #c62828; }
        .countdown-expired { background-color: #eceff1; color: #546e7a; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">GovCore 政务平台</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="budget.php">预决算公开</a></li>
                    <li class="nav-item"><a class="nav-link active" href="recruit.php">公开招聘</a></li>
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

    <div class="container my-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                <li class="breadcrumb-item"><a href="recruit.php" class="text-decoration-none text-muted">公开招聘</a></li>
                <li class="breadcrumb-item active fw-bold text-gov-blue" id="breadcrumbTitle" aria-current="page">岗位详情</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8">
                <div class="detail-section">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="fw-bold text-gov-blue mb-2" id="posTitle">-</h3>
                            <p class="text-muted mb-0"><i class="bi bi-building me-1"></i><span id="posDept">-</span></p>
                        </div>
                        <span class="countdown-big countdown-active" id="countdownBadge">-</span>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="info-item">
                                <label>学历要求</label>
                                <span id="posEdu">-</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <label>招聘人数</label>
                                <span id="posCount">-</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <label>报名截止</label>
                                <span id="posDeadline">-</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <label>已报名人数</label>
                                <span id="posApplyCount">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h5><i class="bi bi-list-task me-2"></i>岗位职责</h5>
                    <div class="content-text" id="posResponsibility">-</div>
                </div>

                <div class="detail-section">
                    <h5><i class="bi bi-person-check me-2"></i>任职要求</h5>
                    <div class="content-text" id="posRequirement">-</div>
                </div>

                <div class="detail-section">
                    <h5><i class="bi bi-pencil-square me-2"></i>报名方式</h5>
                    <div class="content-text" id="posApplyMethod">-</div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-send-check display-4 text-gov-blue mb-3 d-block"></i>
                        <h5 class="fw-bold mb-2">想要报名？</h5>
                        <p class="text-muted small mb-3">点击下方按钮填写报名信息，上传个人简历PDF</p>
                        <button class="btn btn-gov-blue w-100 py-2 fw-bold" id="applyBtn" onclick="openApplyModal()">
                            <i class="bi bi-person-plus-fill me-2"></i>我要报名
                        </button>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-info-circle me-2"></i>温馨提示</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>请如实填写个人信息</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>简历仅支持 PDF 格式，不超过 5MB</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>每个手机号仅可报名同一岗位一次</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i>报名截止后将无法提交申请</li>
                            <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i>如有疑问请拨打12345热线咨询</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="applyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #004d99 0%, #003366 100%); color: white;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>我要报名</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="applyForm">
                        <input type="hidden" id="applyPositionId" value="<?php echo $id; ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">报名岗位</label>
                            <input type="text" class="form-control" id="applyPositionTitle" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">姓名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="applyName" placeholder="请输入姓名" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">手机号 <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="applyPhone" placeholder="请输入手机号" maxlength="11" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">邮箱 <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="applyEmail" placeholder="请输入邮箱地址" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">简历（PDF格式）</label>
                            <input type="file" class="form-control" id="applyResume" accept=".pdf">
                            <div class="form-text">仅支持PDF格式，文件大小不超过5MB</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" id="submitApplyBtn" onclick="submitApply()">
                        <i class="bi bi-send-fill me-1"></i>提交报名
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white-50 py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let positionData = null;

        function loadDetail() {
            const id = <?php echo $id; ?>;
            fetch(`recruit_api.php?action=detail&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        positionData = data.data;
                        renderDetail(data.data);
                    } else {
                        Swal.fire({ title: '错误', text: data.msg, icon: 'error', confirmButtonText: '确定' }).then(() => {
                            window.location.href = 'recruit.php';
                        });
                    }
                });
        }

        function renderDetail(pos) {
            document.getElementById('breadcrumbTitle').textContent = pos.title;
            document.getElementById('posTitle').textContent = pos.title;
            document.getElementById('posDept').textContent = pos.department;
            document.getElementById('posEdu').textContent = pos.education;
            document.getElementById('posCount').textContent = pos.headcount + ' 人';
            document.getElementById('posDeadline').textContent = pos.deadline;
            document.getElementById('posApplyCount').textContent = pos.apply_count + ' 人';
            document.getElementById('posResponsibility').textContent = pos.responsibility || '暂无';
            document.getElementById('posRequirement').textContent = pos.requirement || '暂无';
            document.getElementById('posApplyMethod').textContent = pos.apply_method || '请通过在线报名系统提交申请';
            document.getElementById('applyPositionTitle').value = pos.title;

            const badge = document.getElementById('countdownBadge');
            if (pos.is_expired) {
                badge.className = 'countdown-big countdown-expired';
                badge.innerHTML = '<i class="bi bi-clock-history me-2"></i>已截止';
                document.getElementById('applyBtn').disabled = true;
                document.getElementById('applyBtn').textContent = '报名已截止';
            } else if (pos.remaining_days <= 3) {
                badge.className = 'countdown-big countdown-urgent';
                badge.innerHTML = `<i class="bi bi-alarm me-2"></i>仅剩 ${pos.remaining_days} 天`;
            } else if (pos.remaining_days <= 7) {
                badge.className = 'countdown-big countdown-warning';
                badge.innerHTML = `<i class="bi bi-clock me-2"></i>剩余 ${pos.remaining_days} 天`;
            } else {
                badge.className = 'countdown-big countdown-active';
                badge.innerHTML = `<i class="bi bi-clock me-2"></i>剩余 ${pos.remaining_days} 天`;
            }

            document.title = pos.title + ' - 公开招聘 - GovCore';
        }

        function openApplyModal() {
            if (positionData && positionData.is_expired) {
                Swal.fire({ title: '无法报名', text: '该岗位报名已截止', icon: 'warning', confirmButtonText: '确定' });
                return;
            }
            new bootstrap.Modal(document.getElementById('applyModal')).show();
        }

        function submitApply() {
            const name = document.getElementById('applyName').value.trim();
            const phone = document.getElementById('applyPhone').value.trim();
            const email = document.getElementById('applyEmail').value.trim();
            const positionId = document.getElementById('applyPositionId').value;
            const resumeFile = document.getElementById('applyResume').files[0];

            if (!name) { Swal.fire({ title: '提示', text: '请填写姓名', icon: 'warning', confirmButtonText: '确定' }); return; }
            if (!phone || !/^1[3-9]\d{9}$/.test(phone)) { Swal.fire({ title: '提示', text: '请填写正确的手机号', icon: 'warning', confirmButtonText: '确定' }); return; }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { Swal.fire({ title: '提示', text: '请填写正确的邮箱', icon: 'warning', confirmButtonText: '确定' }); return; }

            if (resumeFile) {
                if (!resumeFile.name.toLowerCase().endsWith('.pdf')) {
                    Swal.fire({ title: '提示', text: '简历仅支持PDF格式', icon: 'warning', confirmButtonText: '确定' });
                    return;
                }
                if (resumeFile.size > 5 * 1024 * 1024) {
                    Swal.fire({ title: '提示', text: '简历文件不能超过5MB', icon: 'warning', confirmButtonText: '确定' });
                    return;
                }
            }

            const formData = new FormData();
            formData.append('position_id', positionId);
            formData.append('name', name);
            formData.append('phone', phone);
            formData.append('email', email);
            if (resumeFile) formData.append('resume', resumeFile);

            document.getElementById('submitApplyBtn').disabled = true;
            document.getElementById('submitApplyBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>提交中...';

            fetch('recruit_api.php?action=apply', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(result => {
                document.getElementById('submitApplyBtn').disabled = false;
                document.getElementById('submitApplyBtn').innerHTML = '<i class="bi bi-send-fill me-1"></i>提交报名';

                if (result.code === 200) {
                    Swal.fire({ title: '报名成功', text: '您的报名信息已提交，请保持手机畅通。', icon: 'success', confirmButtonText: '确定' });
                    bootstrap.Modal.getInstance(document.getElementById('applyModal')).hide();
                    document.getElementById('applyForm').reset();
                    loadDetail();
                } else {
                    Swal.fire({ title: '报名失败', text: result.msg, icon: 'error', confirmButtonText: '确定' });
                }
            })
            .catch(() => {
                document.getElementById('submitApplyBtn').disabled = false;
                document.getElementById('submitApplyBtn').innerHTML = '<i class="bi bi-send-fill me-1"></i>提交报名';
                Swal.fire({ title: '网络错误', text: '请稍后重试', icon: 'error', confirmButtonText: '确定' });
            });
        }

        loadDetail();
    </script>

</body>
</html>
