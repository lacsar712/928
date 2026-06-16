<?php
require_once __DIR__ . '/func.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function json_response($code, $msg, $data = null) {
    echo json_encode(['code' => $code, 'msg' => $msg, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {
    case 'list':
        if ($method !== 'GET') json_response(405, 'Method Not Allowed');

        $department = $_GET['department'] ?? '';
        $keyword = $_GET['keyword'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $page_size = intval($_GET['page_size'] ?? 10);
        if ($page_size <= 0 || $page_size > 100) $page_size = 10;

        $where = ' WHERE 1=1';
        if ($department) {
            $dept = mysqli_real_escape_string($conn, $department);
            $where .= " AND department = '$dept'";
        }
        if ($keyword) {
            $kw = mysqli_real_escape_string($conn, escape_like($keyword));
            $where .= " AND (name LIKE '%$kw%' OR position LIKE '%$kw%' OR responsibility LIKE '%$kw%')";
        }

        $count_sql = "SELECT COUNT(*) as total FROM leaders" . $where;
        $count_res = mysqli_query($conn, $count_sql);
        $total = mysqli_fetch_assoc($count_res)['total'];
        $total_pages = ceil($total / $page_size);
        $offset = ($page - 1) * $page_size;

        $sql = "SELECT * FROM leaders" . $where . " ORDER BY department ASC, sort_order ASC, id ASC LIMIT $offset, $page_size";
        $result = mysqli_query($conn, $sql);
        $list = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $list[] = $row;
        }

        $dept_stats_sql = "SELECT department, COUNT(*) as cnt FROM leaders GROUP BY department";
        $dept_stats_res = mysqli_query($conn, $dept_stats_sql);
        $dept_stats = ['市委' => 0, '市政府' => 0, '人大' => 0, '政协' => 0];
        while ($ds = mysqli_fetch_assoc($dept_stats_res)) {
            if (isset($dept_stats[$ds['department']])) {
                $dept_stats[$ds['department']] = intval($ds['cnt']);
            }
        }

        json_response(200, 'Success', [
            'list' => $list,
            'total' => $total,
            'total_pages' => $total_pages,
            'page' => $page,
            'page_size' => $page_size,
            'dept_stats' => $dept_stats
        ]);
        break;

    case 'detail':
        if ($method !== 'GET') json_response(405, 'Method Not Allowed');

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) json_response(400, 'Invalid ID');

        $sql = "SELECT * FROM leaders WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $leader = mysqli_fetch_assoc($result);
        if (!$leader) json_response(404, 'Record not found');

        json_response(200, 'Success', $leader);
        break;

    case 'add':
        if ($method !== 'POST') json_response(405, 'Method Not Allowed');
        check_login();

        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $responsibility = trim($_POST['responsibility'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (!$name || !$position || !$department) json_response(400, '姓名、职务、部门为必填项');
        if (!in_array($department, ['市委', '市政府', '人大', '政协'])) json_response(400, '部门无效');

        $name = mysqli_real_escape_string($conn, $name);
        $position = mysqli_real_escape_string($conn, $position);
        $department = mysqli_real_escape_string($conn, $department);
        $avatar = mysqli_real_escape_string($conn, $avatar);
        $responsibility = mysqli_real_escape_string($conn, $responsibility);
        $bio = mysqli_real_escape_string($conn, $bio);
        $email = mysqli_real_escape_string($conn, $email);

        $sql = "INSERT INTO leaders (name, position, department, avatar, responsibility, bio, email, sort_order) 
                VALUES ('$name', '$position', '$department', '$avatar', '$responsibility', '$bio', '$email', $sort_order)";
        if (mysqli_query($conn, $sql)) {
            $new_id = mysqli_insert_id($conn);
            Logger::logAction('Leaders', "Added leader: ID=$new_id, $name");
            json_response(200, '添加成功', ['id' => $new_id]);
        } else {
            json_response(500, '添加失败: ' . mysqli_error($conn));
        }
        break;

    case 'edit':
        if ($method !== 'POST') json_response(405, 'Method Not Allowed');
        check_login();

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_response(400, 'Invalid ID');

        $check = mysqli_query($conn, "SELECT id FROM leaders WHERE id = $id");
        if (!mysqli_fetch_assoc($check)) json_response(404, '记录不存在');

        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $responsibility = trim($_POST['responsibility'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (!$name || !$position || !$department) json_response(400, '姓名、职务、部门为必填项');
        if (!in_array($department, ['市委', '市政府', '人大', '政协'])) json_response(400, '部门无效');

        $name = mysqli_real_escape_string($conn, $name);
        $position = mysqli_real_escape_string($conn, $position);
        $department = mysqli_real_escape_string($conn, $department);
        $avatar = mysqli_real_escape_string($conn, $avatar);
        $responsibility = mysqli_real_escape_string($conn, $responsibility);
        $bio = mysqli_real_escape_string($conn, $bio);
        $email = mysqli_real_escape_string($conn, $email);

        $sql = "UPDATE leaders SET 
                name = '$name',
                position = '$position',
                department = '$department',
                avatar = '$avatar',
                responsibility = '$responsibility',
                bio = '$bio',
                email = '$email',
                sort_order = $sort_order
                WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Leaders', "Updated leader: ID=$id, $name");
            json_response(200, '修改成功');
        } else {
            json_response(500, '修改失败: ' . mysqli_error($conn));
        }
        break;

    case 'delete':
        if ($method !== 'POST') json_response(405, 'Method Not Allowed');
        check_login();

        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        if ($id <= 0) json_response(400, 'Invalid ID');

        $check = mysqli_query($conn, "SELECT id, name, avatar FROM leaders WHERE id = $id");
        $leader = mysqli_fetch_assoc($check);
        if (!$leader) json_response(404, '记录不存在');

        if ($leader['avatar'] && file_exists(__DIR__ . '/' . $leader['avatar'])) {
            @unlink(__DIR__ . '/' . $leader['avatar']);
        }

        $sql = "DELETE FROM leaders WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            Logger::logAction('Leaders', "Deleted leader: ID=$id, " . $leader['name']);
            json_response(200, '删除成功');
        } else {
            json_response(500, '删除失败: ' . mysqli_error($conn));
        }
        break;

    case 'upload':
        if ($method !== 'POST') json_response(405, 'Method Not Allowed');
        check_login();

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            json_response(400, '请选择有效的头像文件');
        }

        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed_types)) {
            json_response(400, '仅支持 JPG/PNG/GIF/WebP 格式图片');
        }
        if ($file['size'] > $max_size) {
            json_response(400, '图片大小不能超过 5MB');
        }

        $target_dir = __DIR__ . '/uploads/leaders/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_name = 'leader_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $target_file = $target_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $relative_path = 'uploads/leaders/' . $new_name;
            Logger::logAction('Leaders', "Avatar uploaded: $relative_path");
            json_response(200, '上传成功', ['path' => $relative_path, 'url' => $relative_path]);
        } else {
            json_response(500, '上传失败，请检查目录权限');
        }
        break;

    default:
        json_response(400, 'Invalid action');
}
