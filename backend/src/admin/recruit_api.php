<?php
require_once '../func.php';
check_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        getList();
        break;
    case 'add':
        addPosition();
        break;
    case 'update':
        updatePosition();
        break;
    case 'delete':
        deletePosition();
        break;
    case 'applications':
        getApplications();
        break;
    case 'export_applications':
        exportApplications();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function getList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);
    $status = intval($_GET['status'] ?? -1);
    $keyword = trim($_GET['keyword'] ?? '');

    $where = [];
    if ($status >= 0) {
        $where[] = "status = $status";
    }
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(title LIKE '%$keyword_safe%' OR department LIKE '%$keyword_safe%')";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM recruit_positions $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT p.*, (SELECT COUNT(*) FROM recruit_applications WHERE position_id = p.id) as apply_count FROM recruit_positions p $where_sql ORDER BY p.create_time DESC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $deadline_ts = strtotime($row['deadline'] . ' 23:59:59');
            $now_ts = time();
            $remaining = max(0, ceil(($deadline_ts - $now_ts) / 86400));
            $row['remaining_days'] = $remaining;
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

function addPosition() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $title = trim($input['title'] ?? '');
    $department = trim($input['department'] ?? '');
    $education = trim($input['education'] ?? '');
    $headcount = intval($input['headcount'] ?? 1);
    $deadline = trim($input['deadline'] ?? '');
    $responsibility = trim($input['responsibility'] ?? '');
    $requirement = trim($input['requirement'] ?? '');
    $apply_method = trim($input['apply_method'] ?? '');
    $status = intval($input['status'] ?? 1);

    if (empty($title) || empty($department) || empty($education) || empty($deadline)) {
        echo json_encode(['code' => 400, 'msg' => '岗位名称、招聘单位、学历要求、报名截止日期不能为空']);
        return;
    }

    $title_safe = mysqli_real_escape_string($conn, $title);
    $department_safe = mysqli_real_escape_string($conn, $department);
    $education_safe = mysqli_real_escape_string($conn, $education);
    $deadline_safe = mysqli_real_escape_string($conn, $deadline);
    $responsibility_safe = mysqli_real_escape_string($conn, $responsibility);
    $requirement_safe = mysqli_real_escape_string($conn, $requirement);
    $apply_method_safe = mysqli_real_escape_string($conn, $apply_method);

    $sql = "INSERT INTO recruit_positions (title, department, education, headcount, deadline, responsibility, requirement, apply_method, status) VALUES ('$title_safe', '$department_safe', '$education_safe', $headcount, '$deadline_safe', '$responsibility_safe', '$requirement_safe', '$apply_method_safe', $status)";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('RecruitAdd', "Position: $title, Dept: $department");
        echo json_encode(['code' => 200, 'msg' => '新增成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '新增失败：' . mysqli_error($conn)]);
    }
}

function updatePosition() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '岗位ID不正确']);
        return;
    }

    $fields = [];
    if (isset($input['title'])) {
        $fields[] = "title = '" . mysqli_real_escape_string($conn, trim($input['title'])) . "'";
    }
    if (isset($input['department'])) {
        $fields[] = "department = '" . mysqli_real_escape_string($conn, trim($input['department'])) . "'";
    }
    if (isset($input['education'])) {
        $fields[] = "education = '" . mysqli_real_escape_string($conn, trim($input['education'])) . "'";
    }
    if (isset($input['headcount'])) {
        $fields[] = "headcount = " . intval($input['headcount']);
    }
    if (isset($input['deadline'])) {
        $fields[] = "deadline = '" . mysqli_real_escape_string($conn, trim($input['deadline'])) . "'";
    }
    if (isset($input['responsibility'])) {
        $fields[] = "responsibility = '" . mysqli_real_escape_string($conn, trim($input['responsibility'])) . "'";
    }
    if (isset($input['requirement'])) {
        $fields[] = "requirement = '" . mysqli_real_escape_string($conn, trim($input['requirement'])) . "'";
    }
    if (isset($input['apply_method'])) {
        $fields[] = "apply_method = '" . mysqli_real_escape_string($conn, trim($input['apply_method'])) . "'";
    }
    if (isset($input['status'])) {
        $fields[] = "status = " . intval($input['status']);
    }

    if (empty($fields)) {
        echo json_encode(['code' => 400, 'msg' => '没有可更新的字段']);
        return;
    }

    $sql = "UPDATE recruit_positions SET " . implode(', ', $fields) . " WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('RecruitUpdate', "ID: $id");
        echo json_encode(['code' => 200, 'msg' => '修改成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '修改失败：' . mysqli_error($conn)]);
    }
}

function deletePosition() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '岗位ID不正确']);
        return;
    }

    $sql = "DELETE FROM recruit_positions WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        $del_sql = "DELETE FROM recruit_applications WHERE position_id = $id";
        mysqli_query($conn, $del_sql);
        Logger::logAction('RecruitDelete', "ID: $id");
        echo json_encode(['code' => 200, 'msg' => '删除成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '删除失败：' . mysqli_error($conn)]);
    }
}

function getApplications() {
    global $conn;

    $position_id = intval($_GET['position_id'] ?? 0);
    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);

    $where = [];
    if ($position_id > 0) {
        $where[] = "a.position_id = $position_id";
    }
    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM recruit_applications a $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT a.*, p.title as position_title, p.department FROM recruit_applications a LEFT JOIN recruit_positions p ON a.position_id = p.id $where_sql ORDER BY a.create_time DESC LIMIT $offset, $page_size";
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

function exportApplications() {
    global $conn;

    $position_id = intval($_GET['position_id'] ?? 0);

    $where = [];
    if ($position_id > 0) {
        $where[] = "a.position_id = $position_id";
    }
    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT a.name, a.phone, a.email, a.resume, a.create_time, p.title as position_title, p.department FROM recruit_applications a LEFT JOIN recruit_positions p ON a.position_id = p.id $where_sql ORDER BY a.create_time DESC";
    $result = mysqli_query($conn, $sql);

    $filename = '报名名单';
    if ($position_id > 0) {
        $pos_sql = "SELECT title FROM recruit_positions WHERE id = $position_id";
        $pos_result = mysqli_query($conn, $pos_sql);
        if ($pos_result && $pos_row = mysqli_fetch_assoc($pos_result)) {
            $filename = $pos_row['title'] . '_报名名单';
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    fputcsv($output, ['姓名', '手机号', '邮箱', '报名岗位', '招聘单位', '简历', '报名时间']);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['name'],
                $row['phone'],
                $row['email'],
                $row['position_title'],
                $row['department'],
                $row['resume'] ? '有' : '无',
                $row['create_time']
            ]);
        }
    }

    fclose($output);
    Logger::logAction('RecruitExport', "PositionID: $position_id");
    exit;
}
