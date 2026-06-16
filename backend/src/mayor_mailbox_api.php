<?php
require_once 'func.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'send_code':
        sendSmsCode();
        break;
    case 'submit':
        submitMessage();
        break;
    case 'public_list':
        getPublicList();
        break;
    case 'my_list':
        getMyList();
        break;
    case 'detail':
        getDetail();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function sendSmsCode() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $phone = trim($_POST['phone'] ?? '');

    if (empty($phone)) {
        echo json_encode(['code' => 400, 'msg' => '请输入手机号']);
        return;
    }

    if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
        echo json_encode(['code' => 400, 'msg' => '手机号格式不正确']);
        return;
    }

    $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

    $_SESSION['mayor_mailbox_code'] = $code;
    $_SESSION['mayor_mailbox_code_phone'] = $phone;
    $_SESSION['mayor_mailbox_code_time'] = time();

    Logger::logAction('MayorMailboxSendCode', "Phone: $phone, Code: $code");

    echo json_encode([
        'code' => 200,
        'msg' => '验证码发送成功',
        'data' => [
            'debug_code' => $code,
            'expires_in' => 300
        ]
    ]);
}

function submitMessage() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $name = trim($_POST['name'] ?? '');
    $id_card = trim($_POST['id_card'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $sms_code = trim($_POST['sms_code'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($name) || empty($id_card) || empty($phone) || empty($sms_code) || empty($title) || empty($content)) {
        echo json_encode(['code' => 400, 'msg' => '请填写所有必填项']);
        return;
    }

    if (mb_strlen($name) > 50) {
        echo json_encode(['code' => 400, 'msg' => '姓名长度不能超过50个字符']);
        return;
    }

    if (!preg_match('/^[1-9]\d{5}(18|19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}[\dXx]$/', $id_card)) {
        echo json_encode(['code' => 400, 'msg' => '身份证号格式不正确']);
        return;
    }

    if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
        echo json_encode(['code' => 400, 'msg' => '手机号格式不正确']);
        return;
    }

    if (!isset($_SESSION['mayor_mailbox_code']) || !isset($_SESSION['mayor_mailbox_code_phone']) || !isset($_SESSION['mayor_mailbox_code_time'])) {
        echo json_encode(['code' => 400, 'msg' => '请先获取验证码']);
        return;
    }

    if ($_SESSION['mayor_mailbox_code_phone'] !== $phone) {
        echo json_encode(['code' => 400, 'msg' => '验证码与手机号不匹配']);
        return;
    }

    if (time() - $_SESSION['mayor_mailbox_code_time'] > 300) {
        echo json_encode(['code' => 400, 'msg' => '验证码已过期，请重新获取']);
        return;
    }

    if ($_SESSION['mayor_mailbox_code'] !== $sms_code) {
        echo json_encode(['code' => 400, 'msg' => '验证码不正确']);
        return;
    }

    if (mb_strlen($title) > 255) {
        echo json_encode(['code' => 400, 'msg' => '标题长度不能超过255个字符']);
        return;
    }

    $title = filter_sensitive_words($title);
    $content = filter_sensitive_words($content);

    $message_no = 'SZXX' . date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $name_safe = mysqli_real_escape_string($conn, $name);
    $id_card_safe = mysqli_real_escape_string($conn, $id_card);
    $phone_safe = mysqli_real_escape_string($conn, $phone);
    $title_safe = mysqli_real_escape_string($conn, $title);
    $content_safe = mysqli_real_escape_string($conn, $content);
    $message_no_safe = mysqli_real_escape_string($conn, $message_no);
    $ip_safe = mysqli_real_escape_string($conn, $ip);

    $sql = "INSERT INTO mayor_mailbox (message_no, name, id_card, phone, title, content, ip_address) 
            VALUES ('$message_no_safe', '$name_safe', '$id_card_safe', '$phone_safe', '$title_safe', '$content_safe', '$ip_safe')";

    if (mysqli_query($conn, $sql)) {
        unset($_SESSION['mayor_mailbox_code']);
        unset($_SESSION['mayor_mailbox_code_phone']);
        unset($_SESSION['mayor_mailbox_code_time']);

        Logger::logAction('MayorMailboxSubmit', "Message No: $message_no from $name ($phone)");
        echo json_encode([
            'code' => 200,
            'msg' => '留言提交成功',
            'data' => ['message_no' => $message_no]
        ]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '提交失败：' . mysqli_error($conn)]);
    }
}

function getPublicList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 10);
    $keyword = trim($_GET['keyword'] ?? '');

    if ($page < 1) $page = 1;
    if ($page_size < 1 || $page_size > 50) $page_size = 10;

    $where = ["audit_status = 1", "reply_status = 1", "is_public = 1"];

    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(title LIKE '%$keyword_safe%' OR content LIKE '%$keyword_safe%' OR reply_content LIKE '%$keyword_safe%')";
    }

    $where_sql = ' WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) as total FROM mayor_mailbox $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT id, message_no, name, title, content, reply_content, reply_time, create_time 
            FROM mayor_mailbox $where_sql 
            ORDER BY reply_time DESC 
            LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $name_len = mb_strlen($row['name'], 'UTF-8');
            if ($name_len > 1) {
                $row['name'] = mb_substr($row['name'], 0, 1, 'UTF-8') . str_repeat('*', $name_len - 1);
            }
            $list[] = $row;
        }
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size,
            'total_pages' => ceil($total / $page_size)
        ]
    ]);
}

function getMyList() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $phone = trim($_GET['phone'] ?? '');
        $sms_code = trim($_GET['sms_code'] ?? '');
    } else {
        $phone = trim($_POST['phone'] ?? '');
        $sms_code = trim($_POST['sms_code'] ?? '');
    }

    if (empty($phone) || empty($sms_code)) {
        echo json_encode(['code' => 400, 'msg' => '请填写手机号和验证码']);
        return;
    }

    if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
        echo json_encode(['code' => 400, 'msg' => '手机号格式不正确']);
        return;
    }

    if (!isset($_SESSION['mayor_mailbox_code']) || !isset($_SESSION['mayor_mailbox_code_phone']) || !isset($_SESSION['mayor_mailbox_code_time'])) {
        echo json_encode(['code' => 400, 'msg' => '请先获取验证码']);
        return;
    }

    if ($_SESSION['mayor_mailbox_code_phone'] !== $phone) {
        echo json_encode(['code' => 400, 'msg' => '验证码与手机号不匹配']);
        return;
    }

    if (time() - $_SESSION['mayor_mailbox_code_time'] > 300) {
        echo json_encode(['code' => 400, 'msg' => '验证码已过期，请重新获取']);
        return;
    }

    if ($_SESSION['mayor_mailbox_code'] !== $sms_code) {
        echo json_encode(['code' => 400, 'msg' => '验证码不正确']);
        return;
    }

    $page = intval($_GET['page'] ?? $_POST['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? $_POST['page_size'] ?? 10);

    if ($page < 1) $page = 1;
    if ($page_size < 1 || $page_size > 50) $page_size = 10;

    $phone_safe = mysqli_real_escape_string($conn, $phone);
    $where = "phone = '$phone_safe'";

    $count_sql = "SELECT COUNT(*) as total FROM mayor_mailbox WHERE $where";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT id, message_no, name, title, content, is_public, audit_status, reply_status, reply_content, reply_time, reject_reason, create_time 
            FROM mayor_mailbox WHERE $where 
            ORDER BY create_time DESC 
            LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $list[] = $row;
        }
    }

    $_SESSION['mayor_mailbox_verified_phone'] = $phone;
    $_SESSION['mayor_mailbox_verified_time'] = time();

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size,
            'total_pages' => ceil($total / $page_size)
        ]
    ]);
}

function getDetail() {
    global $conn;

    $id = intval($_GET['id'] ?? 0);
    $message_no = trim($_GET['message_no'] ?? '');

    if ($id <= 0 && empty($message_no)) {
        echo json_encode(['code' => 400, 'msg' => '参数不正确']);
        return;
    }

    if ($id > 0) {
        $sql = "SELECT * FROM mayor_mailbox WHERE id = $id";
    } else {
        $message_no_safe = mysqli_real_escape_string($conn, $message_no);
        $sql = "SELECT * FROM mayor_mailbox WHERE message_no = '$message_no_safe'";
    }

    $result = mysqli_query($conn, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        if ($row['audit_status'] == 1 && $row['reply_status'] == 1 && $row['is_public'] == 1) {
            $name_len = mb_strlen($row['name'], 'UTF-8');
            if ($name_len > 1) {
                $row['name'] = mb_substr($row['name'], 0, 1, 'UTF-8') . str_repeat('*', $name_len - 1);
            }
            unset($row['id_card']);
            unset($row['ip_address']);
            echo json_encode(['code' => 200, 'msg' => 'success', 'data' => $row]);
            return;
        }

        $is_owner = false;
        if (!empty($_SESSION['mayor_mailbox_verified_phone']) && (time() - $_SESSION['mayor_mailbox_verified_time'] < 3600)) {
            if ($_SESSION['mayor_mailbox_verified_phone'] === $row['phone']) {
                $is_owner = true;
            }
        }

        if ($is_owner) {
            unset($row['id_card']);
            unset($row['ip_address']);
            echo json_encode(['code' => 200, 'msg' => 'success', 'data' => $row]);
        } else {
            echo json_encode(['code' => 403, 'msg' => '无权查看此留言']);
        }
    } else {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
    }
}
