<?php
require_once __DIR__ . '/../func.php';

$CACHE_TTL = 300;

function get_aqi_level($aqi) {
    if ($aqi <= 50)  return ['level' => '优', 'color' => '#00e400'];
    if ($aqi <= 100) return ['level' => '良', 'color' => '#FFFF00'];
    if ($aqi <= 150) return ['level' => '轻度污染', 'color' => '#ff7e00'];
    if ($aqi <= 200) return ['level' => '中度污染', 'color' => '#ff0000'];
    if ($aqi <= 300) return ['level' => '重度污染', 'color' => '#99004c'];
    return ['level' => '严重污染', 'color' => '#7e0023'];
}

function get_aqi_advice($aqi) {
    if ($aqi <= 50)  return '空气质量令人满意，基本无空气污染，各类人群可正常活动';
    if ($aqi <= 100) return '空气质量可接受，但某些污染物可能对极少数异常敏感人群健康有较弱影响';
    if ($aqi <= 150) return '易感人群症状有轻度加剧，健康人群出现刺激症状，建议儿童、老人及心脏病、呼吸系统疾病患者减少长时间、高强度的户外锻炼';
    if ($aqi <= 200) return '进一步加剧易感人群症状，可能对健康人群心脏、呼吸系统有影响，建议疾病患者避免长时间、高强度的户外锻炼，一般人群适量减少户外运动';
    if ($aqi <= 300) return '心脏病和肺病患者症状显著加剧，运动耐受力降低，健康人群普遍出现症状，建议儿童、老人和病人停留在室内，避免体力消耗，一般人群避免户外活动';
    return '健康人群运动耐受力降低，有明显强烈症状，提前出现某些疾病，建议儿童、老年人和病人应当留在室内，避免体力消耗，一般人群应避免户外活动';
}

function seeded_rand($seed, $min, $max) {
    mt_srand($seed);
    $val = mt_rand($min, $max);
    mt_srand();
    return $val;
}

function get_cache($key) {
    global $conn, $CACHE_TTL;
    $safe_key = mysqli_real_escape_string($conn, $key);
    $sql = "SELECT * FROM weather_cache WHERE cache_key = '$safe_key'";
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $updated = strtotime($row['updated_at']);
        if (time() - $updated < $CACHE_TTL) {
            return $row;
        }
    }
    return null;
}

function set_cache($key, $data, $seed) {
    global $conn;
    $safe_key = mysqli_real_escape_string($conn, $key);
    $safe_data = mysqli_real_escape_string($conn, $data);
    $safe_seed = intval($seed);
    $sql = "INSERT INTO weather_cache (cache_key, cache_data, seed) VALUES ('$safe_key', '$safe_data', $safe_seed)
            ON DUPLICATE KEY UPDATE cache_data = '$safe_data', seed = $safe_seed, updated_at = NOW()";
    mysqli_query($conn, $sql);
}

function get_weather_config() {
    global $conn;
    $sql = "SELECT config_key, config_value FROM weather_config";
    $result = mysqli_query($conn, $sql);
    $config = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $config[$row['config_key']] = $row['config_value'];
        }
    }
    return $config;
}

function load_mock_data() {
    $mock_file = __DIR__ . '/../data/weather_mock.json';
    if (file_exists($mock_file)) {
        $json = file_get_contents($mock_file);
        return json_decode($json, true);
    }
    return null;
}

function apply_perturbation_today($data, $seed) {
    $data['temp'] = $data['temp'] + seeded_rand($seed, -2, 2);
    $data['temp_high'] = $data['temp_high'] + seeded_rand($seed + 1, -2, 2);
    $data['temp_low'] = $data['temp_low'] + seeded_rand($seed + 2, -2, 2);
    $data['humidity'] = max(10, min(100, $data['humidity'] + seeded_rand($seed + 3, -5, 5)));
    $data['wind_speed'] = max(0, $data['wind_speed'] + seeded_rand($seed + 4, -3, 3));
    $data['aqi'] = max(0, $data['aqi'] + seeded_rand($seed + 5, -10, 10));
    $aqi_info = get_aqi_level($data['aqi']);
    $data['aqi_level'] = $aqi_info['level'];
    $data['aqi_color'] = $aqi_info['color'];
    $data['aqi_advice'] = get_aqi_advice($data['aqi']);
    $data['date'] = date('Y-m-d');
    return $data;
}

function apply_perturbation_forecast($items, $seed) {
    foreach ($items as $i => &$item) {
        $s = $seed + ($i + 1) * 10;
        $item['temp_high'] = $item['temp_high'] + seeded_rand($s, -2, 2);
        $item['temp_low'] = $item['temp_low'] + seeded_rand($s + 1, -2, 2);
        $item['date'] = date('Y-m-d', strtotime("+{$i} day"));
    }
    unset($item);
    return $items;
}

function apply_perturbation_aqi_hourly($items, $seed) {
    foreach ($items as $i => &$item) {
        $s = $seed + $i + 100;
        $item['aqi'] = max(0, $item['aqi'] + seeded_rand($s, -5, 5));
        $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
        $item['hour'] = $hour . ':00';
    }
    unset($item);
    return $items;
}

function apply_perturbation_aqi_weekly($items, $seed) {
    foreach ($items as $i => &$item) {
        $s = $seed + $i + 200;
        $item['aqi'] = max(0, $item['aqi'] + seeded_rand($s, -8, 8));
        $item['date'] = date('Y-m-d', strtotime("-{$i} day"));
    }
    unset($item);
    return $items;
}

function build_today($mock, $seed) {
    return apply_perturbation_today($mock['today'], $seed);
}

function build_forecast($mock, $seed) {
    return apply_perturbation_forecast($mock['forecast'], $seed);
}

function build_aqi($mock, $seed) {
    return [
        'current' => apply_perturbation_today($mock['today'], $seed),
        'hourly' => apply_perturbation_aqi_hourly($mock['aqi_hourly'], $seed),
        'weekly' => apply_perturbation_aqi_weekly($mock['aqi_weekly'], $seed)
    ];
}

function clear_weather_cache() {
    global $conn;
    $sql = "DELETE FROM weather_cache";
    if (mysqli_query($conn, $sql)) {
        $affected = mysqli_affected_rows($conn);
        Logger::logAction('WeatherCache', "手动清除缓存，删除 {$affected} 条记录");
        echo json_encode(['code' => 200, 'msg' => '气象缓存已清除', 'deleted' => $affected], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['code' => 500, 'msg' => '清除失败: ' . mysqli_error($conn)], JSON_UNESCAPED_UNICODE);
    }
}

function handle_weather($action) {
    if ($action === 'clear_cache') {
        clear_weather_cache();
        return;
    }

    $config = get_weather_config();
    $data_source = isset($config['data_source']) ? $config['data_source'] : 'mock';

    if ($data_source === 'real' && !empty($config['real_url'])) {
        handle_real_api($action, $config['real_url']);
        return;
    }

    $cache = get_cache($action);
    if ($cache) {
        echo $cache['cache_data'];
        return;
    }

    $mock = load_mock_data();
    if (!$mock) {
        http_response_code(500);
        echo json_encode(['code' => 500, 'msg' => '气象数据源不可用']);
        return;
    }

    $seed = intval(date('YmdHi'));

    switch ($action) {
        case 'today':
            $data = build_today($mock, $seed);
            break;
        case 'forecast':
            $data = build_forecast($mock, $seed);
            break;
        case 'aqi':
            $data = build_aqi($mock, $seed);
            break;
        default:
            http_response_code(400);
            echo json_encode(['code' => 400, 'msg' => '无效的请求']);
            return;
    }

    $response = json_encode(['code' => 200, 'data' => $data], JSON_UNESCAPED_UNICODE);
    set_cache($action, $response, $seed);
    echo $response;
}

function handle_real_api($action, $base_url) {
    $mapping = [
        'today' => 'now',
        'forecast' => 'forecast',
        'aqi' => 'aqi'
    ];
    $endpoint = isset($mapping[$action]) ? $mapping[$action] : $action;
    $url = rtrim($base_url, '/') . '/' . $endpoint;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        echo $response;
    } else {
        http_response_code(502);
        echo json_encode(['code' => 502, 'msg' => '远程气象接口请求失败'], JSON_UNESCAPED_UNICODE);
    }
}
