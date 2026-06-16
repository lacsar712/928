<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>应急物资台账 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .row-warning { background-color: #fff5f5 !important; }
        .row-warning td { color: #c92f2f !important; font-weight: 600; }
        .warning-badge { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
        .tab-content { min-height: 400px; }
        .nav-tabs .nav-link { color: var(--gov-text-main); font-weight: 500; }
        .nav-tabs .nav-link.active { color: var(--gov-blue-primary); font-weight: 700; border-bottom: 3px solid var(--gov-blue-primary); }
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
                    <a href="index.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i>控制台</a>
                    <a href="emergency.php" class="list-group-item list-group-item-action"><i class="bi bi-exclamation-triangle-fill me-2"></i>应急事件</a>
                    <a href="supply.php" class="list-group-item list-group-item-action active"><i class="bi bi-box-seam me-2"></i>物资台账</a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action"><i class="bi bi-broadcast me-2"></i>网络检测工具</a>
                    <a href="upload.php" class="list-group-item list-group-item-action"><i class="bi bi-cloud-upload me-2"></i>政策文件上传</a>
                    <a href="meeting_rooms.php" class="list-group-item list-group-item-action"><i class="bi bi-door-open me-2"></i>会议室管理</a>
                    <a href="budget.php" class="list-group-item list-group-item-action"><i class="bi bi-cash-stack me-2"></i>预决算管理</a>
                    <a href="mail.php" class="list-group-item list-group-item-action"><i class="bi bi-envelope-open me-2"></i>意见信箱</a>
                    <a href="mayor_mailbox.php" class="list-group-item list-group-item-action"><i class="bi bi-buildings me-2"></i>市长信箱</a>
                    <a href="mail_keywords.php" class="list-group-item list-group-item-action"><i class="bi bi-shield-exclamation me-2"></i>敏感词管理</a>
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action"><i class="bi bi-radar me-2"></i>舆情监测看板</a>
                    <a href="weather_config.php" class="list-group-item list-group-item-action"><i class="bi bi-cloud-sun me-2"></i>气象数据源</a>
                    <a href="recruit.php" class="list-group-item list-group-item-action"><i class="bi bi-briefcase me-2"></i>招聘管理</a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">首页</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue" aria-current="page">应急物资台账</li>
                        </ol>
                    </nav>

                    <ul class="nav nav-tabs mb-4" id="mainTab" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabInventory"><i class="bi bi-box-seam me-1"></i>物资台账</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabStockOp"><i class="bi bi-arrow-left-right me-1"></i>入库/出库</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabWarehouse"><i class="bi bi-building me-1"></i>仓库管理</a></li>
                    </ul>

                    <div class="tab-content">
                        <!-- 物资台账 -->
                        <div class="tab-pane fade show active" id="tabInventory">
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm rounded-3 h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                                            <div><p class="text-muted small mb-1 fw-bold">物资总数</p><h3 class="mb-0 fw-bold text-gov-blue" id="statTotal">0</h3></div>
                                            <div class="icon-shape bg-light-blue text-gov-blue rounded-circle p-3"><i class="bi bi-box-seam fs-4"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm rounded-3 h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                                            <div><p class="text-muted small mb-1 fw-bold">低于安全库存</p><h3 class="mb-0 fw-bold text-danger" id="statWarning">0</h3></div>
                                            <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-circle p-3"><i class="bi bi-exclamation-triangle fs-4"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm rounded-3 h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                                            <div><p class="text-muted small mb-1 fw-bold">仓库数量</p><h3 class="mb-0 fw-bold text-success" id="statWarehouse">0</h3></div>
                                            <div class="icon-shape bg-success bg-opacity-10 text-success rounded-circle p-3"><i class="bi bi-building fs-4"></i></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 shadow-sm rounded-3 h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between p-4">
                                            <div><p class="text-muted small mb-1 fw-bold">物资分类</p><h3 class="mb-0 fw-bold text-info" id="statCategory">0</h3></div>
                                            <div class="icon-shape bg-info bg-opacity-10 text-info rounded-circle p-3"><i class="bi bi-tags fs-4"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-3">
                                            <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" class="form-control" id="supplyKeyword" placeholder="搜索物资名称..."></div>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" id="categoryFilter"><option value="">全部分类</option></select>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="form-select" id="warehouseFilter"><option value="0">全部仓库</option></select>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check"><input class="form-check-input" type="checkbox" id="warningOnly"><label class="form-check-label text-danger fw-bold" for="warningOnly">仅显示告警</label></div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-gov-blue flex-grow-1" onclick="loadSupplies()"><i class="bi bi-funnel me-1"></i>筛选</button>
                                                <button class="btn btn-outline-secondary flex-grow-1" onclick="resetSupplyFilters()"><i class="bi bi-arrow-counterclockwise me-1"></i>重置</button>
                                                <button class="btn btn-success" onclick="showSupplyModal()"><i class="bi bi-plus-lg"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-list-check me-2"></i>物资列表</h5>
                                    <span class="badge bg-secondary" id="supplyTotal">共 0 条</span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>物资名称</th><th>分类</th><th>单位</th><th>库存数量</th><th>安全库存</th><th>状态</th><th>存放仓库</th><th>保质期</th><th>入库时间</th><th>操作</th>
                                                </tr>
                                            </thead>
                                            <tbody id="supplyTableBody"></tbody>
                                        </table>
                                    </div>
                                    <div class="text-center py-3" id="supplyPagination"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 入库/出库 -->
                        <div class="tab-pane fade" id="tabStockOp">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                                        <div class="card-header bg-white border-bottom py-3">
                                            <h5 class="mb-0 fw-bold text-success"><i class="bi bi-box-arrow-in-down me-2"></i>入库登记</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">选择物资</label>
                                                <select class="form-select" id="stockInSupply"></select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">入库数量</label>
                                                <input type="number" class="form-control" id="stockInQty" min="1" value="1">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">操作人</label>
                                                <input type="text" class="form-control" id="stockInOperator" value="<?php echo htmlspecialchars($_SESSION['admin_user']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">备注</label>
                                                <input type="text" class="form-control" id="stockInRemark" placeholder="入库原因...">
                                            </div>
                                            <button class="btn btn-success w-100 fw-bold" onclick="doStockIn()"><i class="bi bi-box-arrow-in-down me-1"></i>确认入库</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                                        <div class="card-header bg-white border-bottom py-3">
                                            <h5 class="mb-0 fw-bold text-danger"><i class="bi bi-box-arrow-up me-2"></i>出库登记</h5>
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">选择物资</label>
                                                <select class="form-select" id="stockOutSupply"></select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">出库数量</label>
                                                <input type="number" class="form-control" id="stockOutQty" min="1" value="1">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">操作人</label>
                                                <input type="text" class="form-control" id="stockOutOperator" value="<?php echo htmlspecialchars($_SESSION['admin_user']); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">备注</label>
                                                <input type="text" class="form-control" id="stockOutRemark" placeholder="出库原因...">
                                            </div>
                                            <button class="btn btn-danger w-100 fw-bold" onclick="doStockOut()"><i class="bi bi-box-arrow-up me-1"></i>确认出库</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: linear-gradient(135deg, #004d99 0%, #003366 100%);">
                                        <div class="card-body p-4 text-white text-center">
                                            <i class="bi bi-clock-history display-3 mb-2"></i>
                                            <p class="mb-1 small opacity-75">近期操作</p>
                                            <h3 class="mb-0 fw-bold" id="recentLogCount">0</h3>
                                            <p class="mb-0 small opacity-75">条记录</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-clock-history me-2"></i>出入库记录</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr><th>物资名称</th><th>类型</th><th>数量</th><th>操作人</th><th>备注</th><th>操作时间</th></tr>
                                            </thead>
                                            <tbody id="stockLogTableBody"></tbody>
                                        </table>
                                    </div>
                                    <div class="text-center py-3" id="stockLogPagination"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 仓库管理 -->
                        <div class="tab-pane fade" id="tabWarehouse">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-building me-2"></i>仓库基础数据</h5>
                                    <button class="btn btn-success" onclick="showWarehouseModal()"><i class="bi bi-plus-lg me-1"></i>新增仓库</button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr><th>ID</th><th>仓库名称</th><th>地址</th><th>负责人</th><th>创建时间</th><th>操作</th></tr>
                                            </thead>
                                            <tbody id="warehouseTableBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 物资编辑 Modal -->
    <div class="modal fade" id="supplyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="supplyModalTitle">新增物资</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="supplyId">
                    <div class="mb-3"><label class="form-label fw-bold">物资名称 *</label><input type="text" class="form-control" id="supplyName"></div>
                    <div class="mb-3"><label class="form-label fw-bold">分类</label><input type="text" class="form-control" id="supplyCategory" placeholder="如：住宿保障、生活保障、医疗救护..."></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">单位</label><input type="text" class="form-control" id="supplyUnit" value="件"></div>
                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">安全库存阈值</label><input type="number" class="form-control" id="supplySafetyStock" min="0" value="0"></div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-bold">存放仓库 *</label><select class="form-select" id="supplyWarehouse"></select></div>
                    <div class="mb-3"><label class="form-label fw-bold">保质期/到期日</label><input type="date" class="form-control" id="supplyExpiry"></div>
                    <div class="mb-3" id="supplyQtyGroup"><label class="form-label fw-bold">初始库存数量</label><input type="number" class="form-control" id="supplyQuantity" min="0" value="0"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveSupply()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 仓库编辑 Modal -->
    <div class="modal fade" id="warehouseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="warehouseModalTitle">新增仓库</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="whId">
                    <div class="mb-3"><label class="form-label fw-bold">仓库名称 *</label><input type="text" class="form-control" id="whName"></div>
                    <div class="mb-3"><label class="form-label fw-bold">地址</label><input type="text" class="form-control" id="whAddress"></div>
                    <div class="mb-3"><label class="form-label fw-bold">负责人</label><input type="text" class="form-control" id="whManager"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue" onclick="saveWarehouse()">保存</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let supplyPage = 1;
        let logPage = 1;
        const pageSize = 15;
        let warehouses = [];
        let categories = [];

        function loadWarehouses() {
            fetch('supply_api.php?action=warehouse_list').then(r => r.json()).then(data => {
                if (data.code === 200) {
                    warehouses = data.data.list;
                    document.getElementById('statWarehouse').textContent = warehouses.length;
                    renderWarehouseTable();
                    populateWarehouseSelects();
                }
            });
        }

        function populateWarehouseSelects() {
            const selects = ['supplyWarehouse', 'warehouseFilter', 'stockInSupply', 'stockOutSupply'];
            selects.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                const val = el.value;
                if (id === 'warehouseFilter') {
                    el.innerHTML = '<option value="0">全部仓库</option>' + warehouses.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
                } else if (id === 'stockInSupply' || id === 'stockOutSupply') {
                    el.innerHTML = '<option value="">请选择物资</option>' + warehouses.map(w =>
                        `<optgroup label="${w.name}">`).join('') + '</optgroup>';
                } else {
                    el.innerHTML = warehouses.map(w => `<option value="${w.id}">${w.name}</option>`).join('');
                }
                el.value = val;
            });
        }

        function loadSupplies() {
            const keyword = document.getElementById('supplyKeyword').value;
            const category = document.getElementById('categoryFilter').value;
            const warehouse_id = document.getElementById('warehouseFilter').value;
            const warning = document.getElementById('warningOnly').checked ? 1 : 0;

            fetch(`supply_api.php?action=supply_list&page=${supplyPage}&page_size=${pageSize}&keyword=${encodeURIComponent(keyword)}&category=${encodeURIComponent(category)}&warehouse_id=${warehouse_id}&warning=${warning}`)
                .then(r => r.json()).then(data => {
                    if (data.code === 200) {
                        renderSupplyTable(data.data.list);
                        renderSupplyPagination(data.data);
                        document.getElementById('supplyTotal').textContent = `共 ${data.data.total} 条`;
                        document.getElementById('statTotal').textContent = data.data.total;
                        document.getElementById('statWarning').textContent = data.data.warning_count;
                        updateCategories(data.data.list);
                    }
                });
        }

        function loadAllSupplies() {
            fetch('supply_api.php?action=supply_all')
                .then(r => r.json()).then(data => {
                    if (data.code === 200) {
                        populateStockSelects(data.data.list);
                    }
                });
        }

        function renderSupplyTable(list) {
            const tbody = document.getElementById('supplyTableBody');
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">暂无物资数据</td></tr>';
                return;
            }
            tbody.innerHTML = list.map(item => {
                const isWarn = item.is_warning;
                const rowClass = isWarn ? 'row-warning' : '';
                const statusHtml = isWarn
                    ? '<span class="badge bg-danger warning-badge"><i class="bi bi-exclamation-triangle-fill me-1"></i>库存不足</span>'
                    : '<span class="badge bg-success">正常</span>';
                return `<tr class="${rowClass}">
                    <td class="fw-bold">${item.name}</td>
                    <td><span class="badge bg-info bg-opacity-10 text-info">${item.category || '-'}</span></td>
                    <td>${item.unit}</td>
                    <td class="fw-bold ${isWarn ? 'text-danger' : ''}">${item.quantity}</td>
                    <td>${item.safety_stock}</td>
                    <td>${statusHtml}</td>
                    <td><i class="bi bi-building me-1"></i>${item.warehouse_name || '-'}</td>
                    <td>${item.expiry_date || '<span class="text-muted">无</span>'}</td>
                    <td class="small">${item.entry_time || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editSupply(${item.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSupply(${item.id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`;
            }).join('');
        }

        function renderSupplyPagination(data) {
            const container = document.getElementById('supplyPagination');
            if (data.total_pages <= 1) { container.innerHTML = ''; return; }
            let html = '<nav><ul class="pagination justify-content-center mb-0">';
            html += `<li class="page-item ${supplyPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="supplyPage=${supplyPage-1};loadSupplies()">上一页</a></li>`;
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= supplyPage - 1 && i <= supplyPage + 1)) {
                    html += `<li class="page-item ${supplyPage === i ? 'active' : ''}"><a class="page-link" onclick="supplyPage=${i};loadSupplies()">${i}</a></li>`;
                } else if (i === supplyPage - 2 || i === supplyPage + 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            html += `<li class="page-item ${supplyPage === data.total_pages ? 'disabled' : ''}"><a class="page-link" onclick="supplyPage=${supplyPage+1};loadSupplies()">下一页</a></li>`;
            html += '</ul></nav>';
            container.innerHTML = html;
        }

        function updateCategories(list) {
            const cats = [...new Set(list.map(i => i.category).filter(Boolean))];
            const sel = document.getElementById('categoryFilter');
            const val = sel.value;
            sel.innerHTML = '<option value="">全部分类</option>' + cats.map(c => `<option value="${c}">${c}</option>`).join('');
            sel.value = val;
            document.getElementById('statCategory').textContent = cats.length;
        }

        function populateStockSelects(list) {
            const grouped = {};
            list.forEach(item => {
                const key = item.warehouse_name || '未分配';
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(item);
            });
            ['stockInSupply', 'stockOutSupply'].forEach(id => {
                const el = document.getElementById(id);
                const val = el.value;
                let html = '<option value="">请选择物资</option>';
                for (const [wh, items] of Object.entries(grouped)) {
                    html += `<optgroup label="${wh}">`;
                    items.forEach(item => {
                        html += `<option value="${item.id}">${item.name} (库存: ${item.quantity}${item.unit})</option>`;
                    });
                    html += '</optgroup>';
                }
                el.innerHTML = html;
                el.value = val;
            });
        }

        function resetSupplyFilters() {
            document.getElementById('supplyKeyword').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('warehouseFilter').value = '0';
            document.getElementById('warningOnly').checked = false;
            supplyPage = 1;
            loadSupplies();
        }

        function showSupplyModal(id) {
            document.getElementById('supplyId').value = '';
            document.getElementById('supplyName').value = '';
            document.getElementById('supplyCategory').value = '';
            document.getElementById('supplyUnit').value = '件';
            document.getElementById('supplySafetyStock').value = 0;
            document.getElementById('supplyWarehouse').value = warehouses.length > 0 ? warehouses[0].id : '';
            document.getElementById('supplyExpiry').value = '';
            document.getElementById('supplyQuantity').value = 0;
            document.getElementById('supplyQtyGroup').style.display = '';
            document.getElementById('supplyModalTitle').textContent = '新增物资';
            new bootstrap.Modal(document.getElementById('supplyModal')).show();
        }

        function editSupply(id) {
            fetch(`supply_api.php?action=supply_list&page=1&page_size=1000`).then(r => r.json()).then(data => {
                if (data.code === 200) {
                    const item = data.data.list.find(i => i.id === id);
                    if (!item) return;
                    document.getElementById('supplyId').value = item.id;
                    document.getElementById('supplyName').value = item.name;
                    document.getElementById('supplyCategory').value = item.category || '';
                    document.getElementById('supplyUnit').value = item.unit;
                    document.getElementById('supplySafetyStock').value = item.safety_stock;
                    document.getElementById('supplyWarehouse').value = item.warehouse_id;
                    document.getElementById('supplyExpiry').value = item.expiry_date || '';
                    document.getElementById('supplyQuantity').value = item.quantity;
                    document.getElementById('supplyQtyGroup').style.display = 'none';
                    document.getElementById('supplyModalTitle').textContent = '编辑物资';
                    new bootstrap.Modal(document.getElementById('supplyModal')).show();
                }
            });
        }

        function saveSupply() {
            const id = document.getElementById('supplyId').value;
            const payload = {
                name: document.getElementById('supplyName').value,
                category: document.getElementById('supplyCategory').value,
                unit: document.getElementById('supplyUnit').value,
                safety_stock: parseInt(document.getElementById('supplySafetyStock').value),
                warehouse_id: parseInt(document.getElementById('supplyWarehouse').value),
                expiry_date: document.getElementById('supplyExpiry').value
            };
            const isEdit = !!id;
            if (!isEdit) {
                payload.quantity = parseInt(document.getElementById('supplyQuantity').value);
                payload.entry_time = new Date().toISOString().slice(0, 19).replace('T', ' ');
            } else {
                payload.id = parseInt(id);
            }
            const action = isEdit ? 'supply_update' : 'supply_create';
            fetch(`supply_api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                if (data.code === 200) {
                    Swal.fire({ title: '成功', text: data.msg, icon: 'success', confirmButtonText: '确定' });
                    bootstrap.Modal.getInstance(document.getElementById('supplyModal')).hide();
                    loadSupplies();
                    loadAllSupplies();
                } else {
                    Swal.fire({ title: '失败', text: data.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function deleteSupply(id) {
            Swal.fire({
                title: '确认删除？', text: '删除后不可恢复，出入库记录也会被清除。', icon: 'warning',
                showCancelButton: true, confirmButtonText: '确定删除', cancelButtonText: '取消'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('supply_api.php?action=supply_delete', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    }).then(r => r.json()).then(data => {
                        if (data.code === 200) { loadSupplies(); loadAllSupplies(); }
                        else { Swal.fire({ title: '失败', text: data.msg, icon: 'error', confirmButtonText: '确定' }); }
                    });
                }
            });
        }

        function doStockIn() {
            const inventory_id = parseInt(document.getElementById('stockInSupply').value);
            const quantity = parseInt(document.getElementById('stockInQty').value);
            const operator = document.getElementById('stockInOperator').value;
            const remark = document.getElementById('stockInRemark').value;
            if (!inventory_id || quantity <= 0) {
                Swal.fire({ title: '提示', text: '请选择物资并输入有效数量', icon: 'warning', confirmButtonText: '确定' });
                return;
            }
            fetch('supply_api.php?action=stock_in', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ inventory_id, quantity, operator, remark })
            }).then(r => r.json()).then(data => {
                if (data.code === 200) {
                    Swal.fire({ title: '入库成功', text: data.msg, icon: 'success', confirmButtonText: '确定' });
                    document.getElementById('stockInQty').value = 1;
                    document.getElementById('stockInRemark').value = '';
                    loadSupplies();
                    loadAllSupplies();
                    loadStockLogs();
                } else {
                    Swal.fire({ title: '失败', text: data.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function doStockOut() {
            const inventory_id = parseInt(document.getElementById('stockOutSupply').value);
            const quantity = parseInt(document.getElementById('stockOutQty').value);
            const operator = document.getElementById('stockOutOperator').value;
            const remark = document.getElementById('stockOutRemark').value;
            if (!inventory_id || quantity <= 0) {
                Swal.fire({ title: '提示', text: '请选择物资并输入有效数量', icon: 'warning', confirmButtonText: '确定' });
                return;
            }
            fetch('supply_api.php?action=stock_out', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ inventory_id, quantity, operator, remark })
            }).then(r => r.json()).then(data => {
                if (data.code === 200) {
                    Swal.fire({ title: '出库成功', text: data.msg, icon: 'success', confirmButtonText: '确定' });
                    document.getElementById('stockOutQty').value = 1;
                    document.getElementById('stockOutRemark').value = '';
                    loadSupplies();
                    loadAllSupplies();
                    loadStockLogs();
                } else {
                    Swal.fire({ title: '失败', text: data.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function loadStockLogs() {
            fetch(`supply_api.php?action=stock_logs&page=${logPage}&page_size=${pageSize}`).then(r => r.json()).then(data => {
                if (data.code === 200) {
                    const list = data.data.list;
                    document.getElementById('recentLogCount').textContent = data.data.total;
                    const tbody = document.getElementById('stockLogTableBody');
                    if (list.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">暂无记录</td></tr>';
                    } else {
                        tbody.innerHTML = list.map(log => {
                            const isIn = log.type === 'in';
                            const typeBadge = isIn
                                ? '<span class="badge bg-success"><i class="bi bi-box-arrow-in-down me-1"></i>入库</span>'
                                : '<span class="badge bg-danger"><i class="bi bi-box-arrow-up me-1"></i>出库</span>';
                            return `<tr>
                                <td class="fw-bold">${log.supply_name || '-'}</td>
                                <td>${typeBadge}</td>
                                <td class="fw-bold ${isIn ? 'text-success' : 'text-danger'}">${isIn ? '+' : '-'}${log.quantity}</td>
                                <td>${log.operator || '-'}</td>
                                <td class="text-muted small">${log.remark || '-'}</td>
                                <td class="small">${log.create_time}</td>
                            </tr>`;
                        }).join('');
                    }
                    renderLogPagination(data.data);
                }
            });
        }

        function renderLogPagination(data) {
            const container = document.getElementById('stockLogPagination');
            if (data.total_pages <= 1) { container.innerHTML = ''; return; }
            let html = '<nav><ul class="pagination justify-content-center mb-0">';
            html += `<li class="page-item ${logPage === 1 ? 'disabled' : ''}"><a class="page-link" onclick="logPage=${logPage-1};loadStockLogs()">上一页</a></li>`;
            for (let i = 1; i <= data.total_pages; i++) {
                if (i === 1 || i === data.total_pages || (i >= logPage - 1 && i <= logPage + 1)) {
                    html += `<li class="page-item ${logPage === i ? 'active' : ''}"><a class="page-link" onclick="logPage=${i};loadStockLogs()">${i}</a></li>`;
                } else if (i === logPage - 2 || i === logPage + 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            html += `<li class="page-item ${logPage === data.total_pages ? 'disabled' : ''}"><a class="page-link" onclick="logPage=${logPage+1};loadStockLogs()">下一页</a></li>`;
            html += '</ul></nav>';
            container.innerHTML = html;
        }

        function renderWarehouseTable() {
            const tbody = document.getElementById('warehouseTableBody');
            if (warehouses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">暂无仓库数据</td></tr>';
                return;
            }
            tbody.innerHTML = warehouses.map(w => `<tr>
                <td>${w.id}</td>
                <td class="fw-bold">${w.name}</td>
                <td>${w.address || '<span class="text-muted">-</span>'}</td>
                <td>${w.manager || '<span class="text-muted">-</span>'}</td>
                <td class="small">${w.create_time}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editWarehouse(${w.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteWarehouse(${w.id})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>`).join('');
        }

        function showWarehouseModal() {
            document.getElementById('whId').value = '';
            document.getElementById('whName').value = '';
            document.getElementById('whAddress').value = '';
            document.getElementById('whManager').value = '';
            document.getElementById('warehouseModalTitle').textContent = '新增仓库';
            new bootstrap.Modal(document.getElementById('warehouseModal')).show();
        }

        function editWarehouse(id) {
            const wh = warehouses.find(w => w.id === id);
            if (!wh) return;
            document.getElementById('whId').value = wh.id;
            document.getElementById('whName').value = wh.name;
            document.getElementById('whAddress').value = wh.address || '';
            document.getElementById('whManager').value = wh.manager || '';
            document.getElementById('warehouseModalTitle').textContent = '编辑仓库';
            new bootstrap.Modal(document.getElementById('warehouseModal')).show();
        }

        function saveWarehouse() {
            const id = document.getElementById('whId').value;
            const payload = {
                name: document.getElementById('whName').value,
                address: document.getElementById('whAddress').value,
                manager: document.getElementById('whManager').value
            };
            const isEdit = !!id;
            if (isEdit) payload.id = parseInt(id);
            const action = isEdit ? 'warehouse_update' : 'warehouse_create';
            fetch(`supply_api.php?action=${action}`, {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(r => r.json()).then(data => {
                if (data.code === 200) {
                    Swal.fire({ title: '成功', text: data.msg, icon: 'success', confirmButtonText: '确定' });
                    bootstrap.Modal.getInstance(document.getElementById('warehouseModal')).hide();
                    loadWarehouses();
                } else {
                    Swal.fire({ title: '失败', text: data.msg, icon: 'error', confirmButtonText: '确定' });
                }
            });
        }

        function deleteWarehouse(id) {
            Swal.fire({
                title: '确认删除？', text: '删除后不可恢复。', icon: 'warning',
                showCancelButton: true, confirmButtonText: '确定删除', cancelButtonText: '取消'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('supply_api.php?action=warehouse_delete', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    }).then(r => r.json()).then(data => {
                        if (data.code === 200) { loadWarehouses(); }
                        else { Swal.fire({ title: '失败', text: data.msg, icon: 'error', confirmButtonText: '确定' }); }
                    });
                }
            });
        }

        document.getElementById('supplyKeyword').addEventListener('keypress', e => {
            if (e.key === 'Enter') { supplyPage = 1; loadSupplies(); }
        });

        document.querySelectorAll('#mainTab a[data-bs-toggle="tab"]').forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', e => {
                if (e.target.getAttribute('href') === '#tabStockOp') {
                    loadAllSupplies();
                    loadStockLogs();
                }
            });
        });

        loadWarehouses();
        loadSupplies();
        loadAllSupplies();
        loadStockLogs();
    </script>
</body>
</html>
