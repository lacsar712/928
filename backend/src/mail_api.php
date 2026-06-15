<?php
require_once 'func.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'submit':
        submitMessage();
        break;
    case 'public_list':
        getPublicList();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function submitMessage() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $is_public = isset($_POST['is_public']) ? intval($_POST['is_public']) : 1;

    if (empty($name) || empty($email) || empty($subject) || empty($content)) {
        echo json_encode(['code' => 400, 'msg' => '请填写所有必填项']);
        return;
    }

    if (mb_strlen($name) > 50) {
        echo json_encode(['code' => 400, 'msg' => '姓名长度不能超过50个字符']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['code' => 400, 'msg' => '邮箱格式不正确']);
        return;
    }

    if (mb_strlen($subject) > 255) {
        echo json_encode(['code' => 400, 'msg' => '主题长度不能超过255个字符']);
        return;
    }

    $subject = filter_sensitive_words($subject);
    $content = filter_sensitive_words($content);

    $message_no = 'MSG' . date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $name_safe = mysqli_real_escape_string($conn, $name);
    $email_safe = mysqli_real_escape_string($conn, $email);
    $subject_safe = mysqli_real_escape_string($conn, $subject);
    $content_safe = mysqli_real_escape_string($conn, $content);
    $message_no_safe = mysqli_real_escape_string($conn, $message_no);
    $ip_safe = mysqli_real_escape_string($conn, $ip);

    $sql = "INSERT INTO mail_messages (message_no, name, email, subject, content, is_public, status, ip_address) 
            VALUES ('$message_no_safe', '$name_safe', '$email_safe', '$subject_safe', '$content_safe', $is_public, 0, '$ip_safe')";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('MailSubmit', "Message No: $message_no from $name");
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

    $where = ["status = 1", "is_public = 1"];

    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(subject LIKE '%$keyword_safe%' OR content LIKE '%$keyword_safe%')";
    }

    $where_sql = ' WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) as total FROM mail_messages $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT id, message_no, name, subject, content, reply_content, reply_time, create_time 
            FROM mail_messages $where_sql 
            ORDER BY create_time DESC 
            LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['name'] = mb_substr($row['name'], 0, 1, 'UTF-8') . str_repeat('*', max(0, mb_strlen($row['name'], 'UTF-8') - 1));
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
