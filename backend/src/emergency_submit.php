<?php
require_once 'func.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['code' => 400, 'msg' => '参数错误']);
    exit;
}

$event_type = trim($input['event_type'] ?? '');
$severity = intval($input['severity'] ?? 0);
$occur_time = trim($input['occur_time'] ?? '');
$location = trim($input['location'] ?? '');
$longitude = $input['longitude'] ?? null;
$latitude = $input['latitude'] ?? null;
$description = trim($input['description'] ?? '');
$images = trim($input['images'] ?? '');
$is_anonymous = intval($input['is_anonymous'] ?? 0);
$reporter_name = trim($input['reporter_name'] ?? '');
$reporter_phone = trim($input['reporter_phone'] ?? '');

$allowed_types = ['自然灾害', '事故灾难', '公共卫生', '社会安全'];
if (!in_array($event_type, $allowed_types)) {
    echo json_encode(['code' => 400, 'msg' => '事件类型不正确']);
    exit;
}

if ($severity < 1 || $severity > 4) {
    echo json_encode(['code' => 400, 'msg' => '严重等级不正确']);
    exit;
}

if (empty($occur_time) || !strtotime($occur_time)) {
    echo json_encode(['code' => 400, 'msg' => '发生时间不正确']);
    exit;
}

if (empty($location)) {
    echo json_encode(['code' => 400, 'msg' => '请填写事发地点']);
    exit;
}

if (empty($description)) {
    echo json_encode(['code' => 400, 'msg' => '请填写现场描述']);
    exit;
}

if (!$is_anonymous) {
    if (empty($reporter_name) || empty($reporter_phone)) {
        echo json_encode(['code' => 400, 'msg' => '非匿名上报需填写姓名和电话']);
        exit;
    }
}

$event_no = 'EMG' . date('YmdHis') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

$ip = $_SERVER['REMOTE_ADDR'];
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

$event_type_safe = mysqli_real_escape_string($conn, $event_type);
$location_safe = mysqli_real_escape_string($conn, $location);
$description_safe = mysqli_real_escape_string($conn, $description);
$images_safe = mysqli_real_escape_string($conn, $images);
$reporter_name_safe = mysqli_real_escape_string($conn, $reporter_name);
$reporter_phone_safe = mysqli_real_escape_string($conn, $reporter_phone);
$event_no_safe = mysqli_real_escape_string($conn, $event_no);
$occur_time_safe = date('Y-m-d H:i:s', strtotime($occur_time));
$ip_safe = mysqli_real_escape_string($conn, $ip);

if ($longitude !== null && $latitude !== null) {
    $longitude = floatval($longitude);
    $latitude = floatval($latitude);
    $sql = "INSERT INTO emergency_events (event_no, event_type, severity, occur_time, location, longitude, latitude, description, images, is_anonymous, reporter_name, reporter_phone, ip_address) 
            VALUES ('$event_no_safe', '$event_type_safe', $severity, '$occur_time_safe', '$location_safe', $longitude, $latitude, '$description_safe', '$images_safe', $is_anonymous, '$reporter_name_safe', '$reporter_phone_safe', '$ip_safe')";
} else {
    $sql = "INSERT INTO emergency_events (event_no, event_type, severity, occur_time, location, description, images, is_anonymous, reporter_name, reporter_phone, ip_address) 
            VALUES ('$event_no_safe', '$event_type_safe', $severity, '$occur_time_safe', '$location_safe', '$description_safe', '$images_safe', $is_anonymous, '$reporter_name_safe', '$reporter_phone_safe', '$ip_safe')";
}

if (mysqli_query($conn, $sql)) {
    Logger::logAction('EmergencySubmit', "Success: $event_no, Type: $event_type, Severity: $severity");
    echo json_encode([
        'code' => 200,
        'msg' => '上报成功',
        'data' => [
            'event_no' => $event_no,
            'severity' => $severity
        ]
    ]);
} else {
    echo json_encode(['code' => 500, 'msg' => '上报失败：' . mysqli_error($conn)]);
}
