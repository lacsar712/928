<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>领导干部信息公示 - GovCore 政务平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .dept-tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            padding: 8px;
            display: flex;
            gap: 4px;
            overflow-x: auto;
        }
        .dept-tab {
            flex: 1;
            min-width: 100px;
            padding: 12px 20px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
            white-space: nowrap;
            cursor: pointer;
        }
        .dept-tab:hover { background: #f8f9fa; color: #004d99; }
        .dept-tab.active {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0,77,153,0.3);
        }
        .dept-tab .bi { margin-right: 6px; }

        .leader-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 16px rgba(0,0,0,0.06);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid rgba(0,0,0,0.04);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .leader-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(0,77,153,0.15);
            border-color: rgba(0,77,153,0.2);
        }
        .card-avatar-wrap {
            background: linear-gradient(160deg, #e6f0ff 0%, #f0f5ff 100%);
            padding: 24px 16px 16px;
            text-align: center;
            position: relative;
        }
        .card-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            background: white;
        }
        .card-avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            margin: 0 auto;
            border: 4px solid white;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -1px;
        }
        .dept-label {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .dept-市委 { background: rgba(21, 101, 192, 0.1); color: #1565c0; }
        .dept-市政府 { background: rgba(230, 81, 0, 0.1); color: #e65100; }
        .dept-人大 { background: rgba(46, 125, 50, 0.1); color: #2e7d32; }
        .dept-政协 { background: rgba(106, 27, 154, 0.1); color: #6a1b9a; }

        .card-body-wrap {
            padding: 16px 18px 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .card-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
            text-align: center;
        }
        .card-position {
            font-size: 0.88rem;
            font-weight: 600;
            color: #004d99;
            text-align: center;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .card-section-title {
            font-size: 0.75rem;
            color: #adb5bd;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .card-section-title .bi { font-size: 0.8rem; }
        .card-resp {
            font-size: 0.82rem;
            color: #495057;
            line-height: 1.5;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-bio {
            font-size: 0.8rem;
            color: #6c757d;
            line-height: 1.5;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-email {
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px dashed #e9ecef;
            font-size: 0.78rem;
            color: #004d99;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-email .bi { font-size: 0.9rem; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #adb5bd;
        }
        .empty-state .bi { font-size: 4rem; opacity: 0.5; margin-bottom: 16px; display: block; }

        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(2px);
        }
        .drawer-overlay.show { opacity: 1; visibility: visible; }

        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            max-width: 520px;
            height: 100vh;
            background: white;
            z-index: 2001;
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: -8px 0 32px rgba(0,0,0,0.15);
        }
        .drawer.show { transform: translateX(0); }

        .drawer-header {
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            color: white;
            padding: 20px 24px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .drawer-close {
            position: absolute;
            top: 16px;
            right: 20px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .drawer-close:hover { background: rgba(255,255,255,0.3); }
        .drawer-hero {
            background: linear-gradient(160deg, #e6f0ff 0%, #f0f5ff 100%);
            padding: 28px 24px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .drawer-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            flex-shrink: 0;
            background: white;
        }
        .drawer-avatar-placeholder {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #004d99 0%, #003366 100%);
            border: 5px solid white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.8rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .drawer-info h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 6px;
            color: #1a1a1a;
        }
        .drawer-info .drawer-position {
            font-size: 0.95rem;
            font-weight: 600;
            color: #004d99;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .drawer-dept-tag {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .drawer-body { padding: 24px; }
        .drawer-section { margin-bottom: 24px; }
        .drawer-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #004d99;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e6f0ff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .drawer-section-title .bi { font-size: 1rem; }
        .drawer-section-content {
            font-size: 0.9rem;
            color: #495057;
            line-height: 1.7;
        }
        .drawer-contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .drawer-contact-item .icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e6f0ff;
            color: #004d99;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 991px) {
            .dept-tab { padding: 10px 14px; font-size: 0.9rem; min-width: 80px; }
            .card-avatar, .card-avatar-placeholder { width: 84px; height: 84px; }
            .card-avatar-placeholder { font-size: 2rem; }
        }

        @media (max-width: 480px) {
            .drawer-hero { flex-direction: column; text-align: center; padding: 20px 16px; }
            .drawer-avatar, .drawer-avatar-placeholder { width: 100px; height: 100px; }
            .drawer-avatar-placeholder { font-size: 2.4rem; }
            .drawer-body { padding: 16px; }
        }

        .leader-card-row {
            margin: 0 -8px;
        }
        .leader-card-col {
            padding: 8px;
        }
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
                    <li class="nav-item"><a class="nav-link active" href="leaders.php">领导信息</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="mayor_mailbox.php">市长信箱</a></li>
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

    <div class="py-4" style="background: linear-gradient(135deg, #004d99 0%, #003366 100%);">
        <div class="container">
            <div class="text-white">
                <h2 class="fw-bold mb-2"><i class="bi bi-people-fill me-2"></i>领导干部信息公示</h2>
                <p class="mb-0 opacity-80">公开领导信息，接受群众监督，打造阳光政务</p>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="dept-tabs mb-4">
            <button class="dept-tab active" data-dept="" onclick="switchDept(this, '')">
                <i class="bi bi-grid-3x3-gap-fill"></i>全部
            </button>
            <button class="dept-tab" data-dept="市委" onclick="switchDept(this, '市委')">
                <i class="bi bi-building-fill"></i>市委
            </button>
            <button class="dept-tab" data-dept="市政府" onclick="switchDept(this, '市政府')">
                <i class="bi bi-bank2"></i>市政府
            </button>
            <button class="dept-tab" data-dept="人大" onclick="switchDept(this, '人大')">
                <i class="bi bi-chat-left-quote-fill"></i>人大
            </button>
            <button class="dept-tab" data-dept="政协" onclick="switchDept(this, '政协')">
                <i class="bi bi-chat-dots-fill"></i>政协
            </button>
        </div>

        <div id="leadersContainer" class="leader-card-row"></div>
        <div id="emptyState" class="empty-state d-none">
            <i class="bi bi-inbox"></i>
            <h5>暂无该部门领导干部信息</h5>
            <p class="mb-0 small">请稍后访问或切换其他部门查看</p>
        </div>
        <div id="loadingState" class="text-center py-5">
            <div class="spinner-border text-gov-blue" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="visually-hidden">加载中...</span>
            </div>
            <p class="mt-3 text-muted small">正在加载领导信息...</p>
        </div>
        <div id="pagination" class="text-center py-4"></div>
    </div>

    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
    <div class="drawer" id="drawer">
        <div class="drawer-header">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>领导信息详情</h5>
            <button class="drawer-close" onclick="closeDrawer()" aria-label="关闭">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div id="drawerContent"></div>
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
        let currentDept = '';
        let currentPage = 1;
        const pageSize = 100;

        function switchDept(btn, dept) {
            document.querySelectorAll('.dept-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            currentDept = dept;
            currentPage = 1;
            loadLeaders();
        }

        function loadLeaders() {
            document.getElementById('loadingState').classList.remove('d-none');
            document.getElementById('leadersContainer').innerHTML = '';
            document.getElementById('emptyState').classList.add('d-none');
            document.getElementById('pagination').innerHTML = '';

            fetch(`leaders_api.php?action=list&page=${currentPage}&page_size=${pageSize}&department=${encodeURIComponent(currentDept)}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('loadingState').classList.add('d-none');
                    if (data.code === 200) {
                        renderCards(data.data.list);
                        if (data.data.list.length === 0) {
                            document.getElementById('emptyState').classList.remove('d-none');
                        }
                    }
                })
                .catch(() => {
                    document.getElementById('loadingState').classList.add('d-none');
                    document.getElementById('emptyState').classList.remove('d-none');
                });
        }

        function renderCards(list) {
            const container = document.getElementById('leadersContainer');
            container.innerHTML = list.map(l => `
                <div class="leader-card-col col-6 col-md-4 col-lg-3 col-xl-3">
                    <div class="leader-card" onclick="openDrawer(${l.id})">
                        <div class="card-avatar-wrap">
                            <span class="dept-label dept-${l.department}">${l.department}</span>
                            ${l.avatar ? `<img src="${l.avatar}" class="card-avatar" alt="${escapeHtml(l.name)}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">` : ''}
                            <div class="card-avatar-placeholder" ${l.avatar ? 'style="display:none;"' : ''}>${l.name.charAt(0)}</div>
                        </div>
                        <div class="card-body-wrap">
                            <div class="card-name">${escapeHtml(l.name)}</div>
                            <div class="card-position">${escapeHtml(l.position)}</div>
                            ${l.responsibility ? `
                                <div class="card-section-title"><i class="bi bi-diagram-3"></i>分管领域</div>
                                <div class="card-resp">${escapeHtml(l.responsibility)}</div>
                            ` : ''}
                            ${l.bio ? `
                                <div class="card-section-title"><i class="bi bi-file-earmark-person"></i>简介</div>
                                <div class="card-bio">${escapeHtml(l.bio)}</div>
                            ` : ''}
                            ${l.email ? `
                                <div class="card-email"><i class="bi bi-envelope-at"></i>${escapeHtml(l.email)}</div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function openDrawer(id) {
            fetch(`leaders_api.php?action=detail&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 200) {
                        const l = data.data;
                        document.getElementById('drawerContent').innerHTML = `
                            <div class="drawer-hero">
                                ${l.avatar ? `<img src="${l.avatar}" class="drawer-avatar" alt="${escapeHtml(l.name)}">` : `<div class="drawer-avatar-placeholder">${l.name.charAt(0)}</div>`}
                                <div class="drawer-info flex-grow-1">
                                    <h2>${escapeHtml(l.name)}</h2>
                                    <div class="drawer-position">${escapeHtml(l.position)}</div>
                                    <span class="drawer-dept-tag dept-${l.department}">${l.department}</span>
                                </div>
                            </div>
                            <div class="drawer-body">
                                ${l.responsibility ? `
                                    <div class="drawer-section">
                                        <div class="drawer-section-title"><i class="bi bi-diagram-3-fill"></i>分管领域</div>
                                        <div class="drawer-section-content">${escapeHtml(l.responsibility)}</div>
                                    </div>
                                ` : ''}
                                ${l.bio ? `
                                    <div class="drawer-section">
                                        <div class="drawer-section-title"><i class="bi bi-file-earmark-person-fill"></i>个人简介</div>
                                        <div class="drawer-section-content">${escapeHtml(l.bio)}</div>
                                    </div>
                                ` : ''}
                                ${l.email ? `
                                    <div class="drawer-section">
                                        <div class="drawer-section-title"><i class="bi bi-telephone-fill"></i>联系方式</div>
                                        <div class="drawer-contact-item">
                                            <div class="icon-wrap"><i class="bi bi-envelope-at-fill"></i></div>
                                            <div>
                                                <div class="small text-muted mb-1">电子邮箱</div>
                                                <a href="mailto:${escapeHtml(l.email)}" class="text-decoration-none text-gov-blue fw-medium">${escapeHtml(l.email)}</a>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                        document.getElementById('drawerOverlay').classList.add('show');
                        document.getElementById('drawer').classList.add('show');
                        document.body.style.overflow = 'hidden';
                    }
                });
        }

        function closeDrawer() {
            document.getElementById('drawerOverlay').classList.remove('show');
            document.getElementById('drawer').classList.remove('show');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeDrawer();
        });

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s || '';
            return d.innerHTML;
        }

        loadLeaders();
    </script>
</body>
</html>
