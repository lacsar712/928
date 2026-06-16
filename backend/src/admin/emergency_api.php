<?php
require_once '../func.php';
check_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        getList();
        break;
    case 'status':
        updateStatus();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function getList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);
    $severity = intval($_GET['severity'] ?? 0);
    $status = intval($_GET['status'] ?? 0);
    $keyword = trim($_GET['keyword'] ?? '');

    $where = [];
    if ($severity > 0) {
        $where[] = "severity = $severity";
    }
    if ($status > 0) {
        $where[] = "status = $status";
    }
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(event_no LIKE '%$keyword_safe%' OR location LIKE '%$keyword_safe%' OR description LIKE '%$keyword_safe%')";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM emergency_events $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $stats_sql = "SELECT status, COUNT(*) as cnt FROM emergency_events $where_sql GROUP BY status";
    $stats_result = mysqli_query($conn, $stats_sql);
    $status_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
    if ($stats_result) {
        while ($row = mysqli_fetch_assoc($stats_result)) {
            $status_counts[intval($row['status'])] = intval($row['cnt']);
        }
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT * FROM emergency_events $where_sql ORDER BY create_time DESC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['images'])) {
                $row['images'] = explode(',', $row['images']);
            } else {
                $row['images'] = [];
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
            'total_pages' => ceil($total / $page_size),
            'status_counts' => $status_counts
        ]
    ]);
}

function updateStatus() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $status = intval($input['status'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '事件ID不正确']);
        return;
    }

    if ($status < 1 || $status > 4) {
        echo json_encode(['code' => 400, 'msg' => '状态值不正确']);
        return;
    }

    $sql = "UPDATE emergency_events SET status = $status WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $status_text = ['', '待处理', '已派单', '已处置', '已归档'][$status];
        Logger::logAction('EmergencyStatus', "ID: $id -> $status_text");
        echo json_encode(['code' => 200, 'msg' => '状态更新成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '更新失败：' . mysqli_error($conn)]);
    }
}
