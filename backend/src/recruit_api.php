<?php
require_once 'func.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        getList();
        break;
    case 'detail':
        getDetail();
        break;
    case 'apply':
        submitApply();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function getList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 10);
    $education = trim($_GET['education'] ?? '');
    $department = trim($_GET['department'] ?? '');
    $keyword = trim($_GET['keyword'] ?? '');

    $where = ["status = 1"];
    if (!empty($education)) {
        $education_safe = mysqli_real_escape_string($conn, $education);
        $where[] = "education = '$education_safe'";
    }
    if (!empty($department)) {
        $department_safe = mysqli_real_escape_string($conn, $department);
        $where[] = "department = '$department_safe'";
    }
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(title LIKE '%$keyword_safe%' OR department LIKE '%$keyword_safe%')";
    }

    $where_sql = ' WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) as total FROM recruit_positions $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT id, title, department, education, headcount, deadline FROM recruit_positions $where_sql ORDER BY deadline ASC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $deadline_ts = strtotime($row['deadline'] . ' 23:59:59');
            $now_ts = time();
            $remaining = max(0, ceil(($deadline_ts - $now_ts) / 86400));
            $row['remaining_days'] = $remaining;
            $row['is_expired'] = ($remaining <= 0) ? 1 : 0;
            $list[] = $row;
        }
    }

    $edu_sql = "SELECT DISTINCT education FROM recruit_positions WHERE status = 1 ORDER BY education";
    $edu_result = mysqli_query($conn, $edu_sql);
    $educations = [];
    if ($edu_result) {
        while ($row = mysqli_fetch_assoc($edu_result)) {
            $educations[] = $row['education'];
        }
    }

    $dept_sql = "SELECT DISTINCT department FROM recruit_positions WHERE status = 1 ORDER BY department";
    $dept_result = mysqli_query($conn, $dept_sql);
    $departments = [];
    if ($dept_result) {
        while ($row = mysqli_fetch_assoc($dept_result)) {
            $departments[] = $row['department'];
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
            'educations' => $educations,
            'departments' => $departments
        ]
    ]);
}

function getDetail() {
    global $conn;

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '岗位ID不正确']);
        return;
    }

    $sql = "SELECT * FROM recruit_positions WHERE id = $id AND status = 1";
    $result = mysqli_query($conn, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $deadline_ts = strtotime($row['deadline'] . ' 23:59:59');
        $now_ts = time();
        $remaining = max(0, ceil(($deadline_ts - $now_ts) / 86400));
        $row['remaining_days'] = $remaining;
        $row['is_expired'] = ($remaining <= 0) ? 1 : 0;

        $app_count_sql = "SELECT COUNT(*) as cnt FROM recruit_applications WHERE position_id = $id";
        $app_result = mysqli_query($conn, $app_count_sql);
        $app_count = 0;
        if ($app_row = mysqli_fetch_assoc($app_result)) {
            $app_count = intval($app_row['cnt']);
        }
        $row['apply_count'] = $app_count;

        echo json_encode([
            'code' => 200,
            'msg' => 'success',
            'data' => $row
        ]);
    } else {
        echo json_encode(['code' => 404, 'msg' => '岗位不存在或已关闭']);
    }
}

function submitApply() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $position_id = intval($_POST['position_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($position_id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '请选择报名岗位']);
        return;
    }
    if (empty($name)) {
        echo json_encode(['code' => 400, 'msg' => '请填写姓名']);
        return;
    }
    if (empty($phone) || !preg_match('/^1[3-9]\d{9}$/', $phone)) {
        echo json_encode(['code' => 400, 'msg' => '请填写正确的手机号']);
        return;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['code' => 400, 'msg' => '请填写正确的邮箱地址']);
        return;
    }

    $check_sql = "SELECT id, deadline, status FROM recruit_positions WHERE id = $position_id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || !($position = mysqli_fetch_assoc($check_result))) {
        echo json_encode(['code' => 400, 'msg' => '岗位不存在']);
        return;
    }
    if (intval($position['status']) !== 1) {
        echo json_encode(['code' => 400, 'msg' => '该岗位已关闭报名']);
        return;
    }
    if (strtotime($position['deadline'] . ' 23:59:59') < time()) {
        echo json_encode(['code' => 400, 'msg' => '该岗位报名已截止']);
        return;
    }

    $name_safe = mysqli_real_escape_string($conn, $name);
    $phone_safe = mysqli_real_escape_string($conn, $phone);
    $email_safe = mysqli_real_escape_string($conn, $email);

    $dup_sql = "SELECT id FROM recruit_applications WHERE position_id = $position_id AND phone = '$phone_safe'";
    $dup_result = mysqli_query($conn, $dup_sql);
    if ($dup_result && mysqli_fetch_assoc($dup_result)) {
        echo json_encode(['code' => 400, 'msg' => '您已报名该岗位，请勿重复报名']);
        return;
    }

    $resume_path = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['resume'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            echo json_encode(['code' => 400, 'msg' => '简历仅支持PDF格式']);
            return;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['code' => 400, 'msg' => '简历文件不能超过5MB']);
            return;
        }

        $upload_dir = UPLOAD_PATH . 'resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = 'resume_' . $position_id . '_' . time() . '_' . mt_rand(1000, 9999) . '.pdf';
        $dest = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $resume_path = 'uploads/resumes/' . $filename;
        }
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    $ip_safe = mysqli_real_escape_string($conn, $ip);

    $sql = "INSERT INTO recruit_applications (position_id, name, phone, email, resume, ip_address) VALUES ($position_id, '$name_safe', '$phone_safe', '$email_safe', '$resume_path', '$ip_safe')";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('RecruitApply', "Position: $position_id, Name: $name, Phone: $phone");
        echo json_encode(['code' => 200, 'msg' => '报名成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '报名失败：' . mysqli_error($conn)]);
    }
}
