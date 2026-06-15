<?php
require_once 'func.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['code' => 400, 'msg' => '请选择要上传的图片']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['code' => 400, 'msg' => '图片上传错误']);
    exit;
}

$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['code' => 400, 'msg' => '仅支持 JPG、PNG、GIF 格式图片']);
    exit;
}

$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['code' => 400, 'msg' => '图片大小不能超过 5MB']);
    exit;
}

$upload_dir = UPLOAD_PATH . 'emergency/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'emg_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
$target_file = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_file)) {
    $relative_path = 'uploads/emergency/' . $filename;
    Logger::logAction('EmergencyUpload', "Success: $relative_path");
    echo json_encode([
        'code' => 200,
        'msg' => '上传成功',
        'data' => [
            'url' => $relative_path
        ]
    ]);
} else {
    echo json_encode(['code' => 500, 'msg' => '上传失败，服务器权限不足']);
}
