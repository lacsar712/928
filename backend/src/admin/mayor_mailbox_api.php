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
    $tab = trim($_GET['tab'] ?? 'unreplied');
    $keyword = trim($_GET['keyword'] ?? '');

    if ($page < 1) $page = 1;
    if ($page_size < 1 || $page_size > 100) $page_size = 10;

    $where = [];
    switch ($tab) {
        case 'unreplied':
            $where[] = "reply_status = 0";
            break;
        case 'replied':
            $where[] = "reply_status = 1";
            break;
        case 'overdue':
            $where[] = "reply_status = 0";
            $where[] = "DATEDIFF(NOW(), create_time) > 15";
            break;
    }

    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(message_no LIKE '%$keyword_safe%' OR name LIKE '%$keyword_safe%' OR phone LIKE '%$keyword_safe%' OR title LIKE '%$keyword_safe%' OR content LIKE '%$keyword_safe%')";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM mayor_mailbox $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT id, message_no, name, id_card, phone, title, is_public, audit_status, reply_status, reply_time, create_time,
                   DATEDIFF(NOW(), create_time) as days_passed
            FROM mayor_mailbox $where_sql 
            ORDER BY create_time DESC 
            LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $id_card = $row['id_card'];
            if (strlen($id_card) == 18) {
                $row['id_card_masked'] = substr($id_card, 0, 6) . '********' . substr($id_card, -4);
            } else {
                $row['id_card_masked'] = $id_card;
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

function getDetail() {
    global $conn;

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    $sql = "SELECT * FROM mayor_mailbox WHERE id = $id";
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
    $audit_status = intval($input['audit_status'] ?? -1);
    $reject_reason = trim($input['reject_reason'] ?? '');

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    if ($audit_status < 1 || $audit_status > 2) {
        echo json_encode(['code' => 400, 'msg' => '审核状态值不正确']);
        return;
    }

    if ($audit_status == 2 && empty($reject_reason)) {
        echo json_encode(['code' => 400, 'msg' => '拒绝时请填写拒绝原因']);
        return;
    }

    $check_sql = "SELECT message_no FROM mayor_mailbox WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || !($check_row = mysqli_fetch_assoc($check_result))) {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
        return;
    }

    $updates = ["audit_status = $audit_status"];
    if ($audit_status == 2) {
        $reject_reason_safe = mysqli_real_escape_string($conn, $reject_reason);
        $updates[] = "reject_reason = '$reject_reason_safe'";
    } else {
        $updates[] = "reject_reason = NULL";
    }

    $update_sql = implode(', ', $updates);
    $sql = "UPDATE mayor_mailbox SET $update_sql WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        $status_text = [1 => '已通过', 2 => '已拒绝'][$audit_status];
        Logger::logAction('MayorMailboxAudit', "ID: $id ({$check_row['message_no']}) -> $status_text by {$_SESSION['admin_user']}");
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
    $is_public = isset($input['is_public']) ? intval($input['is_public']) : null;
    $auto_audit = isset($input['auto_audit']) ? boolval($input['auto_audit']) : true;

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    if (empty($reply_content)) {
        echo json_encode(['code' => 400, 'msg' => '回复内容不能为空']);
        return;
    }

    $check_sql = "SELECT message_no FROM mayor_mailbox WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || !($check_row = mysqli_fetch_assoc($check_result))) {
        echo json_encode(['code' => 404, 'msg' => '记录不存在']);
        return;
    }

    $reply_content = filter_sensitive_words($reply_content);
    $reply_content_safe = mysqli_real_escape_string($conn, $reply_content);
    $admin_safe = mysqli_real_escape_string($conn, $_SESSION['admin_user']);

    $updates = [
        "reply_content = '$reply_content_safe'",
        "reply_time = NOW()",
        "reply_admin = '$admin_safe'",
        "reply_status = 1"
    ];
    if ($is_public !== null && ($is_public == 0 || $is_public == 1)) {
        $updates[] = "is_public = $is_public";
    }
    if ($auto_audit) {
        $updates[] = "audit_status = 1";
        $updates[] = "reject_reason = NULL";
    }

    $update_sql = implode(', ', $updates);
    $sql = "UPDATE mayor_mailbox SET $update_sql WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('MayorMailboxReply', "ID: $id ({$check_row['message_no']}) replied by {$_SESSION['admin_user']}, is_public: " . ($is_public ?? 'unchanged'));
        echo json_encode(['code' => 200, 'msg' => '回复成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '操作失败：' . mysqli_error($conn)]);
    }
}

function getStats() {
    global $conn;

    $stats = [
        'unreplied' => 0,
        'replied' => 0,
        'overdue' => 0,
        'pending_audit' => 0,
        'approved' => 0,
        'rejected' => 0,
        'total' => 0
    ];

    $sql = "SELECT reply_status, audit_status, 
                   CASE WHEN reply_status = 0 AND DATEDIFF(NOW(), create_time) > 15 THEN 1 ELSE 0 END as is_overdue
            FROM mayor_mailbox";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['total']++;
            if ($row['reply_status'] == 0) $stats['unreplied']++;
            if ($row['reply_status'] == 1) $stats['replied']++;
            if ($row['is_overdue'] == 1) $stats['overdue']++;
            if ($row['audit_status'] == 0) $stats['pending_audit']++;
            if ($row['audit_status'] == 1) $stats['approved']++;
            if ($row['audit_status'] == 2) $stats['rejected']++;
        }
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => $stats
    ]);
}
