<?php
require_once 'func.php';

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

function json_response($code, $message, $data = null) {
    echo json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function get_input() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}

switch ($action) {
    case 'rooms':
        handle_rooms($method, $conn);
        break;
    case 'weekly_bookings':
        handle_weekly_bookings($conn);
        break;
    case 'book':
        handle_book($method, $conn);
        break;
    case 'cancel':
        handle_cancel($method, $conn);
        break;
    case 'booking_detail':
        handle_booking_detail($conn);
        break;
    default:
        json_response(404, '接口不存在');
}

function handle_rooms($method, $conn) {
    switch ($method) {
        case 'GET':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $sql = "SELECT * FROM meeting_rooms WHERE id = $id";
                $result = mysqli_query($conn, $sql);
                $room = mysqli_fetch_assoc($result);
                if (!$room) {
                    json_response(404, '会议室不存在');
                }
                $room['equipment_list'] = $room['equipment'] ? array_filter(explode(',', $room['equipment'])) : [];
                json_response(200, '获取成功', $room);
            } else {
                $sql = "SELECT * FROM meeting_rooms ORDER BY floor, name";
                $result = mysqli_query($conn, $sql);
                $rooms = [];
                while ($r = mysqli_fetch_assoc($result)) {
                    $r['equipment_list'] = $r['equipment'] ? array_filter(explode(',', $r['equipment'])) : [];
                    $rooms[] = $r;
                }
                json_response(200, '获取成功', $rooms);
            }
            break;

        case 'POST':
            check_login();
            $input = get_input();
            $name = mysqli_real_escape_string($conn, trim($input['name'] ?? ''));
            $capacity = intval($input['capacity'] ?? 0);
            $floor = mysqli_real_escape_string($conn, trim($input['floor'] ?? ''));
            $equipment = isset($input['equipment']) && is_array($input['equipment']) 
                ? mysqli_real_escape_string($conn, trim(implode(',', array_filter($input['equipment']))))
                : '';
            $status = intval($input['status'] ?? 1);

            if (!$name || $capacity <= 0 || !$floor) {
                json_response(400, '请填写完整的会议室信息（名称、容量、楼层）');
            }

            $sql = "INSERT INTO meeting_rooms (name, capacity, floor, equipment, status) 
                    VALUES ('$name', $capacity, '$floor', '$equipment', $status)";
            if (mysqli_query($conn, $sql)) {
                $id = mysqli_insert_id($conn);
                Logger::logAction('MeetingRoom', "创建会议室: $name");
                json_response(200, '创建成功', ['id' => $id]);
            } else {
                json_response(500, '创建失败: ' . mysqli_error($conn));
            }
            break;

        case 'PUT':
            check_login();
            $input = get_input();
            $id = intval($input['id'] ?? 0);
            $name = mysqli_real_escape_string($conn, trim($input['name'] ?? ''));
            $capacity = intval($input['capacity'] ?? 0);
            $floor = mysqli_real_escape_string($conn, trim($input['floor'] ?? ''));
            $equipment = isset($input['equipment']) && is_array($input['equipment']) 
                ? mysqli_real_escape_string($conn, trim(implode(',', array_filter($input['equipment']))))
                : '';
            $status = intval($input['status'] ?? 1);

            if (!$id || !$name || $capacity <= 0 || !$floor) {
                json_response(400, '请填写完整的会议室信息');
            }

            $sql = "UPDATE meeting_rooms SET name='$name', capacity=$capacity, floor='$floor', 
                    equipment='$equipment', status=$status WHERE id=$id";
            if (mysqli_query($conn, $sql)) {
                Logger::logAction('MeetingRoom', "更新会议室ID={$id}, 名称={$name}");
                json_response(200, '更新成功');
            } else {
                json_response(500, '更新失败: ' . mysqli_error($conn));
            }
            break;

        case 'DELETE':
            check_login();
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) {
                json_response(400, '缺少会议室ID');
            }

            $check = mysqli_query($conn, "SELECT * FROM meeting_rooms WHERE id=$id");
            if (!mysqli_fetch_assoc($check)) {
                json_response(404, '会议室不存在');
            }

            $sql = "DELETE FROM meeting_rooms WHERE id=$id";
            if (mysqli_query($conn, $sql)) {
                mysqli_query($conn, "DELETE FROM meeting_bookings WHERE room_id=$id");
                Logger::logAction('MeetingRoom', "删除会议室ID={$id}");
                json_response(200, '删除成功');
            } else {
                json_response(500, '删除失败: ' . mysqli_error($conn));
            }
            break;

        default:
            json_response(405, '不支持的请求方法');
    }
}

function handle_weekly_bookings($conn) {
    $week_start = isset($_GET['week_start']) ? $_GET['week_start'] : '';
    $room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

    if (!$week_start) {
        $monday = strtotime('monday this week');
        $week_start = date('Y-m-d', $monday);
    }

    $start_dt = date('Y-m-d 00:00:00', strtotime($week_start));
    $end_dt = date('Y-m-d 23:59:59', strtotime($week_start . ' +6 days'));

    $sql = "SELECT b.*, r.name as room_name, r.capacity, r.floor 
             FROM meeting_bookings b 
             LEFT JOIN meeting_rooms r ON b.room_id = r.id 
             WHERE b.start_time <= '$end_dt' AND b.end_time >= '$start_dt' 
             AND b.status = 1";

    if ($room_id > 0) {
        $sql .= " AND b.room_id = $room_id";
    }

    $sql .= " ORDER BY b.start_time ASC";

    $result = mysqli_query($conn, $sql);
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }

    $week_dates = [];
    for ($i = 0; $i < 7; $i++) {
        $week_dates[] = date('Y-m-d', strtotime($week_start . " +$i days"));
    }

    json_response(200, '获取成功', [
        'week_start' => $week_start,
        'week_dates' => $week_dates,
        'bookings' => $bookings
    ]);
}

function check_time_conflict($conn, $room_id, $start_time, $end_time, $exclude_id = 0) {
    $st = mysqli_real_escape_string($conn, $start_time);
    $et = mysqli_real_escape_string($conn, $end_time);

    $sql = "SELECT * FROM meeting_bookings 
            WHERE room_id = $room_id AND status = 1
            AND id != $exclude_id
            AND start_time < '$et' AND end_time > '$st'";

    $result = mysqli_query($conn, $sql);
    $conflicts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $conflicts[] = $row;
    }
    return $conflicts;
}

function handle_book($method, $conn) {
    if ($method !== 'POST') {
        json_response(405, '不支持的请求方法');
    }

    check_login();

    $input = get_input();
    $room_id = intval($input['room_id'] ?? 0);
    $subject = mysqli_real_escape_string($conn, trim($input['subject'] ?? ''));
    $attendees = intval($input['attendees'] ?? 0);
    $start_time = trim($input['start_time'] ?? '');
    $end_time = trim($input['end_time'] ?? '');
    $booker = mysqli_real_escape_string($conn, trim($input['booker'] ?? ''));

    if (!$room_id || !$subject || $attendees <= 0 || !$start_time || !$end_time || !$booker) {
        json_response(400, '请填写完整的预约信息（主题、参会人数、起止时间、预订人）');
    }

    if (strtotime($start_time) >= strtotime($end_time)) {
        json_response(400, '结束时间必须晚于开始时间');
    }

    $start_hour = intval(date('H', strtotime($start_time)));
    $end_hour = intval(date('H', strtotime($end_time)));
    $end_minute = intval(date('i', strtotime($end_time)));
    if ($start_hour < 8 || $end_hour > 20 || ($end_hour == 20 && $end_minute > 0)) {
        json_response(400, '预约时间必须在 08:00 - 20:00 之间');
    }

    $room_sql = "SELECT * FROM meeting_rooms WHERE id = $room_id AND status = 1";
    $room_result = mysqli_query($conn, $room_sql);
    $room = mysqli_fetch_assoc($room_result);
    if (!$room) {
        json_response(404, '会议室不存在或已禁用');
    }

    if ($attendees > $room['capacity']) {
        json_response(400, "参会人数超出会议室容量（最大容纳 {$room['capacity']} 人）");
    }

    $conflicts = check_time_conflict($conn, $room_id, $start_time, $end_time);
    if (!empty($conflicts)) {
        $conflict_msgs = [];
        foreach ($conflicts as $c) {
            $conflict_msgs[] = "【{$c['subject']}】" . date('H:i', strtotime($c['start_time'])) . '-' . date('H:i', strtotime($c['end_time'])) . "，预订人：{$c['booker']}";
        }
        json_response(409, '时间冲突，该时段已被预约：' . implode('；', $conflict_msgs));
    }

    $sql = "INSERT INTO meeting_bookings (room_id, subject, attendees, start_time, end_time, booker, status)
            VALUES ($room_id, '$subject', $attendees, '$start_time', '$end_time', '$booker', 1)";

    if (mysqli_query($conn, $sql)) {
        $id = mysqli_insert_id($conn);
        Logger::logAction('MeetingBooking', "预约成功: $subject, 会议室ID={$room_id}, {$start_time} ~ {$end_time}, 预订人={$booker}");
        json_response(200, '预约成功', ['id' => $id]);
    } else {
        json_response(500, '预约失败: ' . mysqli_error($conn));
    }
}

function handle_cancel($method, $conn) {
    if ($method !== 'POST') {
        json_response(405, '不支持的请求方法');
    }

    check_login();
    $input = get_input();
    $id = intval($input['id'] ?? 0);

    if (!$id) {
        json_response(400, '缺少预约ID');
    }

    $check = mysqli_query($conn, "SELECT * FROM meeting_bookings WHERE id=$id");
    $booking = mysqli_fetch_assoc($check);
    if (!$booking) {
        json_response(404, '预约记录不存在');
    }

    $sql = "UPDATE meeting_bookings SET status = 0 WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('MeetingBooking', "取消预约ID={$id}, 主题={$booking['subject']}");
        json_response(200, '取消成功');
    } else {
        json_response(500, '取消失败: ' . mysqli_error($conn));
    }
}

function handle_booking_detail($conn) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$id) {
        json_response(400, '缺少预约ID');
    }

    $sql = "SELECT b.*, r.name as room_name, r.capacity, r.floor, r.equipment
            FROM meeting_bookings b
            LEFT JOIN meeting_rooms r ON b.room_id = r.id
            WHERE b.id = $id";
    $result = mysqli_query($conn, $sql);
    $booking = mysqli_fetch_assoc($result);
    if (!$booking) {
        json_response(404, '预约记录不存在');
    }
    $booking['equipment_list'] = $booking['equipment'] ? array_filter(explode(',', $booking['equipment'])) : [];
    json_response(200, '获取成功', $booking);
}
