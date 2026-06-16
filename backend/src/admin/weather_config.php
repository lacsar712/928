<?php
require_once '../func.php';
check_login();

$msg = '';
$msg_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_source = isset($_POST['data_source']) ? $_POST['data_source'] : 'mock';
    $mock_url = isset($_POST['mock_url']) ? trim($_POST['mock_url']) : '';
    $real_url = isset($_POST['real_url']) ? trim($_POST['real_url']) : '';

    if (!in_array($data_source, ['mock', 'real'])) {
        $data_source = 'mock';
    }

    $safe_ds = mysqli_real_escape_string($conn, $data_source);
    $safe_mu = mysqli_real_escape_string($conn, $mock_url);
    $safe_ru = mysqli_real_escape_string($conn, $real_url);

    $sql1 = "INSERT INTO weather_config (config_key, config_value) VALUES ('data_source', '$safe_ds') ON DUPLICATE KEY UPDATE config_value = '$safe_ds'";
    $sql2 = "INSERT INTO weather_config (config_key, config_value) VALUES ('mock_url', '$safe_mu') ON DUPLICATE KEY UPDATE config_value = '$safe_mu'";
    $sql3 = "INSERT INTO weather_config (config_key, config_value) VALUES ('real_url', '$safe_ru') ON DUPLICATE KEY UPDATE config_value = '$safe_ru'";

    if (mysqli_query($conn, $sql1) && mysqli_query($conn, $sql2) && mysqli_query($conn, $sql3)) {
        $msg = '气象数据源配置已保存';
        $msg_type = 'success';
        Logger::logAction('WeatherConfig', "数据源切换为: $data_source");
    } else {
        $msg = '保存失败: ' . mysqli_error($conn);
        $msg_type = 'danger';
    }

    $clear_sql = "DELETE FROM weather_cache";
    mysqli_query($conn, $clear_sql);
}

$config = [];
$result = mysqli_query($conn, "SELECT config_key, config_value FROM weather_config");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $config[$row['config_key']] = $row['config_value'];
    }
}

$data_source = isset($config['data_source']) ? $config['data_source'] : 'mock';
$mock_url = isset($config['mock_url']) ? $config['mock_url'] : '';
$real_url = isset($config['real_url']) ? $config['real_url'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>气象数据源配置 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    
    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="#">GovCore 管理中心</a>
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
                    <a href="mayor_mailbox.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-buildings me-2"></i>市长信箱
                    </a>
                    <a href="mail_keywords.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-exclamation me-2"></i>敏感词管理
                    </a>
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-radar me-2"></i>舆情监测看板
                    </a>
                    <a href="weather_config.php" class="list-group-item list-group-item-action active">
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">控制台</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue">气象数据源配置</li>
                        </ol>
                    </nav>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 text-gov-blue fw-bold"><i class="bi bi-cloud-sun me-2"></i>气象数据源配置</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info-emphasis mb-4">
                                <i class="bi bi-info-circle-fill me-2"></i>配置气象数据来源。选择"Mock 数据"使用内置模拟数据，选择"真实接口"可对接外部气象 API。切换后缓存自动清除。
                            </div>
                            
                            <form method="POST" class="needs-validation">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">数据源类型</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="data_source" id="dsMock" value="mock" <?php echo $data_source === 'mock' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="dsMock">
                                            <i class="bi bi-database me-1"></i>Mock 数据（内置模拟，默认）
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="data_source" id="dsReal" value="real" <?php echo $data_source === 'real' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="dsReal">
                                            <i class="bi bi-globe me-1"></i>真实接口（对接外部气象 API）
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="mock_url" class="form-label fw-bold">Mock 数据路径 <small class="text-muted fw-normal">（留空使用默认内置 JSON）</small></label>
                                    <input type="text" class="form-control" id="mock_url" name="mock_url" 
                                           value="<?php echo htmlspecialchars($mock_url); ?>" 
                                           placeholder="例：/data/weather_mock.json">
                                </div>

                                <div class="mb-4">
                                    <label for="real_url" class="form-label fw-bold">真实接口 Base URL</label>
                                    <input type="text" class="form-control" id="real_url" name="real_url" 
                                           value="<?php echo htmlspecialchars($real_url); ?>" 
                                           placeholder="例：https://api.weather.com/v2">
                                    <div class="form-text text-muted">
                                        系统将在 Base URL 后追加 <code>/now</code>、<code>/forecast</code>、<code>/aqi</code> 分别请求今日、预报、AQI 数据
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-gov-blue px-4 py-2">
                                    <i class="bi bi-check-lg me-2"></i>保存配置
                                </button>
                                <button type="button" class="btn btn-outline-danger ms-2" id="clearCacheBtn">
                                    <i class="bi bi-trash3 me-2"></i>清除气象缓存
                                </button>
                            </form>

                            <hr class="my-4">

                            <h6 class="fw-bold text-gov-blue mb-3"><i class="bi bi-eye me-2"></i>当前配置状态</h6>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <div class="p-3 bg-light rounded border">
                                        <small class="text-muted d-block">数据源</small>
                                        <span class="fw-bold <?php echo $data_source === 'mock' ? 'text-success' : 'text-primary'; ?>">
                                            <?php echo $data_source === 'mock' ? 'Mock 数据' : '真实接口'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-3 bg-light rounded border">
                                        <small class="text-muted d-block">真实接口 URL</small>
                                        <span class="fw-bold"><?php echo $real_url ? htmlspecialchars($real_url) : '未配置'; ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-3 bg-light rounded border">
                                        <small class="text-muted d-block">缓存有效期</small>
                                        <span class="fw-bold">5 分钟</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($msg): ?>
    <script>
        Swal.fire({
            title: '<?php echo $msg_type == "success" ? "成功" : "失败"; ?>',
            text: '<?php echo htmlspecialchars($msg); ?>',
            icon: '<?php echo $msg_type == "success" ? "success" : "error"; ?>',
            confirmButtonText: '确定'
        });
    </script>
    <?php endif; ?>
    <script>
        document.getElementById('clearCacheBtn').addEventListener('click', function() {
            Swal.fire({
                title: '确认清除缓存?',
                text: "此操作将清除所有气象缓存数据，下次请求将重新生成。",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '立即清除',
                cancelButtonText: '取消'
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('../api/weather/today?_nocache=1').catch(function(){});
                    fetch('../api/weather/forecast?_nocache=1').catch(function(){});
                    fetch('../api/weather/aqi?_nocache=1').catch(function(){});
                    Swal.fire('已清除!', '气象缓存已清除，下次请求将重新生成数据。', 'success');
                }
            });
        });
    </script>

</body>
</html>
