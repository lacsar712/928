<?php
require_once 'func.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>突发事件应急上报 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .emergency-header {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .severity-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            color: white;
            font-size: 1.2rem;
        }
        .severity-1 { background-color: #dc3545; }
        .severity-2 { background-color: #fd7e14; }
        .severity-3 { background-color: #ffc107; color: #000; }
        .severity-4 { background-color: #0d6efd; }
        .severity-badge.selected {
            border-color: #333;
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .type-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid #dee2e6;
        }
        .type-card:hover {
            border-color: #004d99;
            transform: translateY(-3px);
        }
        .type-card.selected {
            border-color: #004d99;
            background-color: #e6f0ff;
        }
        .map-container {
            position: relative;
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #a8e6cf 0%, #88d8b0 100%);
            border-radius: 8px;
            overflow: hidden;
            cursor: crosshair;
        }
        .map-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        .map-marker {
            position: absolute;
            transform: translate(-50%, -100%);
            font-size: 2rem;
            color: #dc3545;
            pointer-events: none;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .image-upload-box {
            width: 100px;
            height: 100px;
            border: 2px dashed #ced4da;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .image-upload-box:hover {
            border-color: #004d99;
            color: #004d99;
        }
        .image-item {
            position: relative;
            display: inline-block;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
        .image-remove {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="mayor_mailbox.php">市长信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="budget.php">预决算公开</a></li>
                    <li class="nav-item"><a class="nav-link active bg-danger rounded" href="emergency_report.php"><i class="bi bi-exclamation-triangle-fill me-1"></i>应急上报</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin/login.php">管理登录</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="emergency-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-3"></i>突发事件应急上报</h1>
                    <p class="lead mb-0 opacity-90">快速响应，及时处置，共同守护公共安全</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <div class="display-1 opacity-20">110</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form id="reportForm">
                    <!-- 事件类型 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-clipboard2-event me-2"></i>事件类型</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="card type-card p-3 text-center" data-type="自然灾害">
                                        <div class="display-5 text-primary mb-2"><i class="bi bi-cloud-lightning-rain"></i></div>
                                        <h6 class="mb-0">自然灾害</h6>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card type-card p-3 text-center" data-type="事故灾难">
                                        <div class="display-5 text-danger mb-2"><i class="bi bi-fire"></i></div>
                                        <h6 class="mb-0">事故灾难</h6>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card type-card p-3 text-center" data-type="公共卫生">
                                        <div class="display-5 text-success mb-2"><i class="bi bi-heart-pulse"></i></div>
                                        <h6 class="mb-0">公共卫生</h6>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card type-card p-3 text-center" data-type="社会安全">
                                        <div class="display-5 text-warning mb-2"><i class="bi bi-shield-exclamation"></i></div>
                                        <h6 class="mb-0">社会安全</h6>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="event_type" id="event_type" required>
                        </div>
                    </div>

                    <!-- 严重等级 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-sign-stop-fill me-2"></i>严重等级</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="severity-badge severity-1" data-severity="1">Ⅰ级</div>
                                    <p class="mt-2 mb-0 small text-muted">特别重大</p>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="severity-badge severity-2" data-severity="2">Ⅱ级</div>
                                    <p class="mt-2 mb-0 small text-muted">重大</p>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="severity-badge severity-3" data-severity="3">Ⅲ级</div>
                                    <p class="mt-2 mb-0 small text-muted">较大</p>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="severity-badge severity-4" data-severity="4">Ⅳ级</div>
                                    <p class="mt-2 mb-0 small text-muted">一般</p>
                                </div>
                            </div>
                            <input type="hidden" name="severity" id="severity" required>
                        </div>
                    </div>

                    <!-- 发生时间 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-clock-fill me-2"></i>发生时间</h5>
                        </div>
                        <div class="card-body">
                            <input type="datetime-local" class="form-control form-control-lg" name="occur_time" id="occur_time" required>
                        </div>
                    </div>

                    <!-- 事发地点 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>事发地点</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">地点描述</label>
                                <input type="text" class="form-control form-control-lg" name="location" id="location" placeholder="请输入详细地址" required>
                            </div>
                            <label class="form-label fw-bold">地图选点（点击地图选择精确位置）</label>
                            <div class="map-container" id="mapContainer">
                                <div class="map-grid"></div>
                                <div id="mapMarker" class="map-marker" style="display: none;"><i class="bi bi-geo-alt-fill"></i></div>
                                <div class="position-absolute bottom-2 end-2 bg-white px-2 py-1 rounded small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>点击地图选择位置
                                </div>
                            </div>
                            <div class="mt-2 small text-muted" id="coordInfo">
                                经度：-- | 纬度：--
                            </div>
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="latitude" id="latitude">
                        </div>
                    </div>

                    <!-- 现场描述 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-chat-left-text-fill me-2"></i>现场描述</h5>
                        </div>
                        <div class="card-body">
                            <textarea class="form-control form-control-lg" name="description" id="description" rows="4" placeholder="请详细描述事件情况，包括人员伤亡、财产损失、现场状况等..." required></textarea>
                        </div>
                    </div>

                    <!-- 现场图片 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-image-fill me-2"></i>现场图片 <small class="text-muted fw-normal">（最多3张）</small></h5>
                        </div>
                        <div class="card-body">
                            <div id="imageContainer">
                                <div class="image-upload-box" id="uploadBtn">
                                    <div class="text-center">
                                        <i class="bi bi-plus-lg display-6"></i>
                                        <div class="small">添加图片</div>
                                    </div>
                                </div>
                                <input type="file" id="imageInput" accept="image/*" style="display: none;">
                            </div>
                            <input type="hidden" name="images" id="images">
                        </div>
                    </div>

                    <!-- 上报人信息 -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-person-fill me-2"></i>上报人信息</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_anonymous">
                                    <label class="form-check-label fw-bold" for="is_anonymous">
                                        <i class="bi bi-incognito me-1"></i>匿名上报
                                    </label>
                                </div>
                            </div>
                            <div id="reporterInfo">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">姓名</label>
                                        <input type="text" class="form-control form-control-lg" name="reporter_name" id="reporter_name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">联系电话</label>
                                        <input type="tel" class="form-control form-control-lg" name="reporter_phone" id="reporter_phone">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-lg btn-danger py-3 fw-bold" id="submitBtn">
                            <i class="bi bi-send-fill me-2"></i>提交上报
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='index.php'">
                            <i class="bi bi-house me-2"></i>返回首页
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white-50 py-4">
        <div class="container text-center">
            <p class="mb-1">主办单位：XX市人民政府办公室 | 技术支持：XX大数据中心</p>
            <p class="mb-0 small">Copyright &copy; 2024 GovCore System. All Rights Reserved. | 建议使用 Chrome 或 Edge 浏览器访问</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const MAP_CENTER_LNG = 116.3972;
        const MAP_CENTER_LAT = 39.9075;
        const MAP_RANGE = 0.1;

        let selectedType = '';
        let selectedSeverity = 0;
        let uploadedImages = [];

        document.querySelectorAll('.type-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                selectedType = this.dataset.type;
                document.getElementById('event_type').value = selectedType;
            });
        });

        document.querySelectorAll('.severity-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                document.querySelectorAll('.severity-badge').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedSeverity = parseInt(this.dataset.severity);
                document.getElementById('severity').value = selectedSeverity;
            });
        });

        document.getElementById('occur_time').value = new Date().toISOString().slice(0, 16);

        const mapContainer = document.getElementById('mapContainer');
        const mapMarker = document.getElementById('mapMarker');
        const coordInfo = document.getElementById('coordInfo');
        let selectedLng = null;
        let selectedLat = null;

        mapContainer.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            mapMarker.style.left = x + 'px';
            mapMarker.style.top = y + 'px';
            mapMarker.style.display = 'block';

            const relX = x / rect.width;
            const relY = y / rect.height;

            selectedLng = (MAP_CENTER_LNG - MAP_RANGE + relX * MAP_RANGE * 2).toFixed(7);
            selectedLat = (MAP_CENTER_LAT + MAP_RANGE - relY * MAP_RANGE * 2).toFixed(7);

            document.getElementById('longitude').value = selectedLng;
            document.getElementById('latitude').value = selectedLat;
            coordInfo.textContent = `经度：${selectedLng} | 纬度：${selectedLat}`;
        });

        document.getElementById('is_anonymous').addEventListener('change', function() {
            const reporterInfo = document.getElementById('reporterInfo');
            if (this.checked) {
                reporterInfo.style.display = 'none';
                document.getElementById('reporter_name').value = '';
                document.getElementById('reporter_phone').value = '';
            } else {
                reporterInfo.style.display = 'block';
            }
        });

        document.getElementById('uploadBtn').addEventListener('click', function() {
            if (uploadedImages.length >= 3) {
                Swal.fire({
                    title: '提示',
                    text: '最多只能上传3张图片',
                    icon: 'warning',
                    confirmButtonText: '知道了'
                });
                return;
            }
            document.getElementById('imageInput').click();
        });

        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            fetch('emergency_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.code === 200) {
                    uploadedImages.push(data.data.url);
                    renderImages();
                } else {
                    Swal.fire({
                        title: '上传失败',
                        text: data.msg,
                        icon: 'error',
                        confirmButtonText: '确定'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: '上传失败',
                    text: '网络错误，请重试',
                    icon: 'error',
                    confirmButtonText: '确定'
                });
            });

            e.target.value = '';
        });

        function renderImages() {
            const container = document.getElementById('imageContainer');
            let html = '';

            uploadedImages.forEach((img, index) => {
                html += `
                    <div class="image-item">
                        <img src="${img}" class="image-preview" alt="图片${index + 1}">
                        <button type="button" class="image-remove" onclick="removeImage(${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
            });

            if (uploadedImages.length < 3) {
                html += `
                    <div class="image-upload-box" id="uploadBtn">
                        <div class="text-center">
                            <i class="bi bi-plus-lg display-6"></i>
                            <div class="small">添加图片</div>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;
            document.getElementById('images').value = uploadedImages.join(',');

            document.getElementById('uploadBtn')?.addEventListener('click', function() {
                if (uploadedImages.length >= 3) {
                    Swal.fire({
                        title: '提示',
                        text: '最多只能上传3张图片',
                        icon: 'warning',
                        confirmButtonText: '知道了'
                    });
                    return;
                }
                document.getElementById('imageInput').click();
            });
        }

        function removeImage(index) {
            uploadedImages.splice(index, 1);
            renderImages();
        }

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedType) {
                Swal.fire({ title: '请选择事件类型', icon: 'warning', confirmButtonText: '确定' });
                return;
            }
            if (!selectedSeverity) {
                Swal.fire({ title: '请选择严重等级', icon: 'warning', confirmButtonText: '确定' });
                return;
            }

            const isAnonymous = document.getElementById('is_anonymous').checked;
            if (!isAnonymous) {
                if (!document.getElementById('reporter_name').value.trim()) {
                    Swal.fire({ title: '请输入姓名', icon: 'warning', confirmButtonText: '确定' });
                    return;
                }
                if (!document.getElementById('reporter_phone').value.trim()) {
                    Swal.fire({ title: '请输入联系电话', icon: 'warning', confirmButtonText: '确定' });
                    return;
                }
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>提交中...';

            const data = {
                event_type: selectedType,
                severity: selectedSeverity,
                occur_time: document.getElementById('occur_time').value,
                location: document.getElementById('location').value,
                description: document.getElementById('description').value,
                images: uploadedImages.join(','),
                is_anonymous: isAnonymous ? 1 : 0,
                reporter_name: document.getElementById('reporter_name').value,
                reporter_phone: document.getElementById('reporter_phone').value
            };

            if (selectedLng !== null && selectedLat !== null) {
                data.longitude = parseFloat(selectedLng);
                data.latitude = parseFloat(selectedLat);
            }

            fetch('emergency_submit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.code === 200) {
                    const severity = result.data.severity;
                    let bgColor = '';
                    let title = '';
                    if (severity === 1) { bgColor = '#dc3545'; title = '特别重大事件已登记'; }
                    else if (severity === 2) { bgColor = '#fd7e14'; title = '重大事件已登记'; }
                    else if (severity === 3) { bgColor = '#ffc107'; title = '较大事件已登记'; }
                    else { bgColor = '#0d6efd'; title = '一般事件已登记'; }

                    Swal.fire({
                        title: title,
                        html: `
                            <p class="mb-2">您的事件已成功上报，我们将尽快处理。</p>
                            <div class="alert alert-light mb-0">
                                <p class="mb-1">事件编号：</p>
                                <h4 class="fw-bold mb-0" style="color: ${bgColor};">${result.data.event_no}</h4>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: '我知道了',
                        confirmButtonColor: bgColor,
                        background: '#fff'
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        title: '上报失败',
                        text: result.msg,
                        icon: 'error',
                        confirmButtonText: '确定'
                    });
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>提交上报';
                }
            })
            .catch(() => {
                Swal.fire({
                    title: '上报失败',
                    text: '网络错误，请重试',
                    icon: 'error',
                    confirmButtonText: '确定'
                });
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send-fill me-2"></i>提交上报';
            });
        });
    </script>
</body>
</html>
