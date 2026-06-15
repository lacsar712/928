<?php
require_once '../func.php';
check_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        getAdminList();
        break;
    case 'detail':
        getDetail();
        break;
    case 'audit':
        auditMessage();
        break;
    case 'reply':
        replyMessage();
        break;
    case 'sensitive_list':
        getSensitiveList();
        break;
    case 'sensitive_add':
        addSensitiveWord();
        break;
    case 'sensitive_delete':
        deleteSensitiveWord();
        break;
    case 'stats':
        getStats();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function getAdminList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 10);
    $status = intval($_GET['status'] ?? -1);
    $keyword = trim($_GET['keyword'] ?? '');

    if ($page < 1) $page = 1;
    if ($page_size < 1 || $page_size > 100) $page_size = 10;

    $where = [];
    if ($status >= 0 && $status <= 2) {
        $where[] = "status = $status";
    }
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(message_no LIKE '%$keyword_safe%' OR name LIKE '%$keyword_safe%' OR email LIKE '%$keyword_safe%' OR subject LIKE '%$keyword_safe%')";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM mail_messages $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT id, message_no, name, email, subject, is_public, status, reply_content, reply_time, reply_admin, create_time 
            FROM mail_messages $where_sql 
            ORDER BY create_time DESC 
            LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
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

function getDetail() {
    global $conn;

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    $sql = "SELECT * FROM mail_messages WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        echo json_encode(['code' => 200, 'msg' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
    }
}

function auditMessage() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $status = intval($input['status'] ?? -1);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    if ($status < 0 || $status > 2) {
        echo json_encode(['code' => 400, 'msg' => '状态值不正确']);
        return;
    }

    $check_sql = "SELECT message_no, status FROM mail_messages WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || !mysqli_fetch_assoc($check_result)) {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
        return;
    }

    $sql = "UPDATE mail_messages SET status = $status WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $status_text = [0 => '待审', 1 => '已通过', 2 => '已拒绝'][$status];
        $check_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT message_no FROM mail_messages WHERE id = $id"));
        Logger::logAction('MailAudit', "ID: $id ({$check_row['message_no']}) -> $status_text by {$_SESSION['admin_user']}");
        echo json_encode(['code' => 200, 'msg' => '审核操作成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '操作失败：' . mysqli_error($conn)]);
    }
}

function replyMessage() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $reply_content = trim($input['reply_content'] ?? '');
    $status = isset($input['status']) ? intval($input['status']) : null;

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    if (empty($reply_content)) {
        echo json_encode(['code' => 400, 'msg' => '回复内容不能为空']);
        return;
    }

    $check_sql = "SELECT message_no FROM mail_messages WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || !mysqli_fetch_assoc($check_result)) {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
        return;
    }

    $reply_content = filter_sensitive_words($reply_content);
    $reply_content_safe = mysqli_real_escape_string($conn, $reply_content);
    $admin_safe = mysqli_real_escape_string($conn, $_SESSION['admin_user']);

    $updates = ["reply_content = '$reply_content_safe'", "reply_time = NOW()", "reply_admin = '$admin_safe'"];
    if ($status !== null && $status >= 0 && $status <= 2) {
        $updates[] = "status = $status";
    }

    $update_sql = implode(', ', $updates);
    $sql = "UPDATE mail_messages SET $update_sql WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        $check_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT message_no FROM mail_messages WHERE id = $id"));
        Logger::logAction('MailReply', "ID: $id ({$check_row['message_no']}) replied by {$_SESSION['admin_user']}");
        echo json_encode(['code' => 200, 'msg' => '回复成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '操作失败：' . mysqli_error($conn)]);
    }
}

function getSensitiveList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);
    $keyword = trim($_GET['keyword'] ?? '');

    if ($page < 1) $page = 1;
    if ($page_size < 1 || $page_size > 100) $page_size = 20;

    $where = [];
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "word LIKE '%$keyword_safe%'";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM mail_sensitive_words $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT * FROM mail_sensitive_words $where_sql ORDER BY id DESC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
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

function addSensitiveWord() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $word = trim($input['word'] ?? '');

    if (empty($word)) {
        echo json_encode(['code' => 400, 'msg' => '敏感词不能为空']);
        return;
    }

    if (mb_strlen($word, 'UTF-8') > 100) {
        echo json_encode(['code' => 400, 'msg' => '敏感词长度不能超过100个字符']);
        return;
    }

    $word_safe = mysqli_real_escape_string($conn, $word);
    $sql = "INSERT INTO mail_sensitive_words (word) VALUES ('$word_safe')";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('SensitiveWordAdd', "Word: $word by {$_SESSION['admin_user']}");
        echo json_encode(['code' => 200, 'msg' => '添加成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        if (mysqli_errno($conn) == 1062) {
            echo json_encode(['code' => 400, 'msg' => '该敏感词已存在']);
        } else {
            echo json_encode(['code' => 500, 'msg' => '添加失败：' . mysqli_error($conn)]);
        }
    }
}

function deleteSensitiveWord() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    $check_sql = "SELECT word FROM mail_sensitive_words WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || !($check_row = mysqli_fetch_assoc($check_result))) {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
        return;
    }

    $sql = "DELETE FROM mail_sensitive_words WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('SensitiveWordDelete', "Word: {$check_row['word']} by {$_SESSION['admin_user']}");
        echo json_encode(['code' => 200, 'msg' => '删除成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '删除失败：' . mysqli_error($conn)]);
    }
}

function getStats() {
    global $conn;

    $stats = [0 => 0, 1 => 0, 2 => 0];

    $sql = "SELECT status, COUNT(*) as cnt FROM mail_messages GROUP BY status";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $stats[intval($row['status'])] = intval($row['cnt']);
        }
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'pending' => $stats[0],
            'approved' => $stats[1],
            'rejected' => $stats[2],
            'total' => array_sum($stats)
        ]
    ]);
}
