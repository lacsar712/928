<?php
require_once 'func.php';

$logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会议室预约系统 - GovCore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .calendar-container { overflow-x: auto; }
        .calendar-table { table-layout: fixed; border-collapse: separate; border-spacing: 1px; background: #dee2e6; width: 100%; min-width: 900px; }
        .calendar-table th, .calendar-table td { background: #fff; padding: 0; vertical-align: top; }
        .calendar-table thead th { background: #f8f9fa; padding: 10px 4px; text-align: center; font-weight: 600; border: none; }
        .calendar-table thead th.today-cell { background: #e3f2fd; color: #1565c0; }
        .calendar-table .time-col { width: 70px; background: #f8f9fa; text-align: center; padding: 6px 2px; font-size: 12px; color: #666; font-weight: 500; }
        .calendar-table .time-col.half-hour { background: #fafafa; color: #999; font-size: 11px; }
        .calendar-cell { height: 20px; cursor: pointer; position: relative; transition: background 0.15s; }
        .calendar-cell:hover { background: #e3f2fd; }
        .calendar-cell.half-hour { height: 20px; }
        .booking-block {
            position: absolute; left: 2px; right: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff; border-radius: 4px; padding: 4px 6px; font-size: 11px;
            overflow: hidden; z-index: 10; cursor: pointer;
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .booking-block:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); z-index: 20; }
        .booking-block .bb-title { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .booking-block .bb-info { font-size: 10px; opacity: 0.9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .week-nav-btn { min-width: 40px; }
        .room-selector .btn-check:checked + .btn-outline-primary { background: #004d99; border-color: #004d99; }
        .equipment-tag-sm { display: inline-block; background: #e3f2fd; color: #1565c0; padding: 1px 6px; border-radius: 3px; font-size: 11px; margin: 1px; }
        .login-prompt-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center; z-index: 9999;
        }
        .legend-item { display: inline-flex; align-items: center; margin-right: 16px; font-size: 12px; }
        .legend-color { width: 16px; height: 16px; border-radius: 3px; margin-right: 6px; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-gov-blue shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-calendar-check me-2"></i>会议室预约系统
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>返回首页</a></li>
                    <li class="nav-item"><a class="nav-link" href="mail.php">意见信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="mayor_mailbox.php">市长信箱</a></li>
                    <li class="nav-item"><a class="nav-link" href="budget.php">预决算公开</a></li>
                    <?php if ($logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link bg-light text-primary rounded px-3 ms-2 fw-bold" href="admin/meeting_rooms.php">
                                <i class="bi bi-gear me-1"></i>会议室管理
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="admin/logout.php"><i class="bi bi-box-arrow-right me-1"></i>退出</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link bg-white text-primary rounded px-3 ms-2 fw-bold" href="admin/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>管理员登录</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-primary week-nav-btn" onclick="changeWeek(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="text-center px-3">
                            <h5 class="mb-0 fw-bold text-gov-blue" id="weekRangeText">--</h5>
                            <small class="text-muted" id="weekRangeSub">选择周视图</small>
                        </div>
                        <button class="btn btn-outline-primary week-nav-btn" onclick="changeWeek(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button class="btn btn-light ms-2" onclick="goToday()">
                            <i class="bi bi-calendar-event me-1"></i>本周
                        </button>
                    </div>
                    <div>
                        <span class="legend-item"><span class="legend-color" style="background: linear-gradient(135deg, #667eea, #764ba2);"></span>已预约</span>
                        <span class="legend-item"><span class="legend-color" style="background: #fff; border: 1px dashed #dee2e6;"></span>可预约</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-gov-blue"><i class="bi bi-door-open me-2"></i>选择会议室</h6>
            </div>
            <div class="card-body p-4">
                <div class="btn-group flex-wrap room-selector" id="roomSelector" role="group">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="calendar-container p-3" id="calendarWrap">
                    <table class="calendar-table" id="calendarTable">
                        <thead id="calendarHead"></thead>
                        <tbody id="calendarBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-gov-blue text-white">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>预约会议室</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 mb-3 small" id="roomInfoAlert">
                        <i class="bi bi-info-circle me-1"></i><span id="roomInfoText">--</span>
                    </div>
                    <input type="hidden" id="b_room_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">会议主题 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="b_subject" placeholder="请输入会议主题">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">开始时间 <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="b_start" step="1800" min="08:00" max="20:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">结束时间 <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="b_end" step="1800" min="08:00" max="20:00">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">参会人数 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="b_attendees" min="1" placeholder="请输入人数">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">预订人 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="b_booker" placeholder="请输入姓名">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue px-4" onclick="submitBooking()">
                        <i class="bi bi-check-lg me-2"></i>确认预约
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                    <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>预约详情</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detailContent">
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">关闭</button>
                    <?php if ($logged_in): ?>
                        <button type="button" class="btn btn-danger px-4" id="cancelBookingBtn">
                            <i class="bi bi-x-lg me-2"></i>取消预约
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const bookingModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
        const weekDays = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        let currentWeekStart = null;
        let rooms = [];
        let currentRoomId = null;
        let weekBookings = [];
        let currentDetailId = null;

        function formatDate(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        function formatDateTime(d) {
            return formatDate(d) + 'T' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        }

        function getMonday(date) {
            const d = new Date(date);
            const day = d.getDay() || 7;
            d.setDate(d.getDate() - day + 1);
            d.setHours(0, 0, 0, 0);
            return d;
        }

        function initWeek() {
            currentWeekStart = getMonday(new Date());
            updateWeekDisplay();
        }

        function changeWeek(delta) {
            currentWeekStart = new Date(currentWeekStart.getTime() + delta * 7 * 24 * 60 * 60 * 1000);
            updateWeekDisplay();
        }

        function goToday() {
            currentWeekStart = getMonday(new Date());
            updateWeekDisplay();
        }

        function updateWeekDisplay() {
            const end = new Date(currentWeekStart.getTime() + 6 * 24 * 60 * 60 * 1000);
            const m1 = currentWeekStart.getMonth() + 1;
            const d1 = currentWeekStart.getDate();
            const m2 = end.getMonth() + 1;
            const d2 = end.getDate();
            document.getElementById('weekRangeText').textContent = `${m1}月${d1}日 - ${m2}月${d2}日`;
            document.getElementById('weekRangeSub').textContent = `${currentWeekStart.getFullYear()}年 第${getWeekNumber(currentWeekStart)}周`;
            loadWeeklyBookings();
        }

        function getWeekNumber(d) {
            const target = new Date(d.valueOf());
            const dayNr = (d.getDay() + 6) % 7;
            target.setDate(target.getDate() - dayNr + 3);
            const firstThursday = new Date(target.getFullYear(), 0, 4);
            const diff = target - firstThursday;
            return 1 + Math.round(((diff / 86400000) - 3 + ((firstThursday.getDay() + 6) % 7)) / 7);
        }

        async function loadRooms() {
            const res = await fetch('meeting_api.php?action=rooms');
            const data = await res.json();
            if (data.code === 200) {
                rooms = data.data.filter(r => r.status == 1);
                renderRoomSelector();
                if (rooms.length > 0) {
                    selectRoom(rooms[0].id);
                }
            }
        }

        function renderRoomSelector() {
            const container = document.getElementById('roomSelector');
            if (rooms.length === 0) {
                container.innerHTML = '<div class="text-muted py-3">暂无可预约的会议室，请联系管理员配置</div>';
                return;
            }
            container.innerHTML = rooms.map((r, idx) => `
                <input type="radio" class="btn-check" name="room" id="room_${r.id}" autocomplete="off" ${idx === 0 ? 'checked' : ''}>
                <label class="btn btn-outline-primary me-2 mb-2" for="room_${r.id}" onclick="selectRoom(${r.id})">
                    <i class="bi bi-door-open me-1"></i><strong>${r.name}</strong>
                    <small class="d-block opacity-75 mt-1">${r.floor} · 容纳${r.capacity}人</small>
                </label>
            `).join('');
        }

        function selectRoom(id) {
            currentRoomId = id;
            loadWeeklyBookings();
        }

        async function loadWeeklyBookings() {
            if (!currentRoomId) return;
            const url = `meeting_api.php?action=weekly_bookings&week_start=${formatDate(currentWeekStart)}&room_id=${currentRoomId}`;
            const res = await fetch(url);
            const data = await res.json();
            if (data.code === 200) {
                weekBookings = data.data.bookings;
                renderCalendar();
            }
        }

        function renderCalendar() {
            const head = document.getElementById('calendarHead');
            const body = document.getElementById('calendarBody');
            const today = formatDate(new Date());
            const room = rooms.find(r => r.id == currentRoomId);

            let headHtml = '<tr><th class="time-col" style="width:70px;">时间</th>';
            for (let i = 0; i < 7; i++) {
                const d = new Date(currentWeekStart.getTime() + i * 24 * 60 * 60 * 1000);
                const dateStr = formatDate(d);
                const isToday = dateStr === today;
                headHtml += `<th class="${isToday ? 'today-cell' : ''}">
                    <div>${weekDays[i]}</div>
                    <small class="${isToday ? 'fw-bold' : 'text-muted'}">${d.getMonth() + 1}/${d.getDate()}${isToday ? ' 今天' : ''}</small>
                </th>`;
            }
            headHtml += '</tr>';
            head.innerHTML = headHtml;

            let bodyHtml = '';
            for (let h = 8; h <= 19; h++) {
                for (let half = 0; half < 2; half++) {
                    const hour = String(h).padStart(2, '0');
                    const minute = half === 0 ? '00' : '30';
                    const isHalf = half === 1;
                    bodyHtml += `<tr>`;
                    bodyHtml += `<td class="time-col ${isHalf ? 'half-hour' : ''}" style="height:20px;">${hour}:${minute}</td>`;
                    for (let day = 0; day < 7; day++) {
                        const d = new Date(currentWeekStart.getTime() + day * 24 * 60 * 60 * 1000);
                        const dateStr = formatDate(d);
                        const cellTime = `${dateStr} ${hour}:${minute}:00`;
                        const cellId = `cell_${day}_${h}_${half}`;
                        bodyHtml += `<td class="calendar-cell ${isHalf ? 'half-hour' : ''}" 
                                        id="${cellId}" 
                                        data-date="${dateStr}" 
                                        data-hour="${h}" 
                                        data-half="${half}"
                                        onclick="handleCellClick('${dateStr}', ${h}, ${half}, ${day})"></td>`;
                    }
                    bodyHtml += '</tr>';
                }
            }
            body.innerHTML = bodyHtml;
            renderBookings();
        }

        function renderBookings() {
            const CELL_HEIGHT = 20;
            const SLOT_MINUTES = 30;
            const START_HOUR = 8;

            weekBookings.forEach(b => {
                const start = new Date(b.start_time.replace(' ', 'T'));
                const end = new Date(b.end_time.replace(' ', 'T'));
                const dateStr = formatDate(start);
                const dayIdx = Math.round((new Date(dateStr + 'T00:00:00') - new Date(formatDate(currentWeekStart) + 'T00:00:00')) / (24 * 60 * 60 * 1000));
                if (dayIdx < 0 || dayIdx > 6) return;

                const startMinFrom8 = start.getHours() * 60 + start.getMinutes() - START_HOUR * 60;
                const endMinFrom8 = end.getHours() * 60 + end.getMinutes() - START_HOUR * 60;
                if (endMinFrom8 <= 0 || startMinFrom8 >= 12 * 60) return;

                const startSlot = Math.floor(startMinFrom8 / SLOT_MINUTES);
                const totalSlots = 24;

                if (startSlot >= 0 && startSlot < totalSlots) {
                    const startH = START_HOUR + Math.floor(startSlot / 2);
                    const startHalf = startSlot % 2;
                    const cellId = `cell_${dayIdx}_${startH}_${startHalf}`;
                    const targetCell = document.getElementById(cellId);
                    if (!targetCell) return;

                    const pxOffset = (startMinFrom8 - startSlot * SLOT_MINUTES) * (CELL_HEIGHT / SLOT_MINUTES);
                    const pxHeight = Math.max(CELL_HEIGHT, (endMinFrom8 - startMinFrom8) * (CELL_HEIGHT / SLOT_MINUTES) - 2);

                    const block = document.createElement('div');
                    block.className = 'booking-block';
                    block.style.cssText = `
                        position: absolute;
                        top: ${pxOffset + 2}px;
                        left: 2px;
                        right: 2px;
                        height: ${pxHeight}px;
                        z-index: 10;
                    `;
                    block.innerHTML = `
                        <div class="bb-title">${escapeHtml(b.subject)}</div>
                        <div class="bb-info">${formatTime(b.start_time)}-${formatTime(b.end_time)} · ${escapeHtml(b.booker)}</div>
                    `;
                    block.onclick = (e) => {
                        e.stopPropagation();
                        showDetail(b.id);
                    };
                    targetCell.style.position = 'relative';
                    targetCell.appendChild(block);
                }
            });
        }

        function formatTime(dtStr) {
            const d = new Date(dtStr.replace(' ', 'T'));
            return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        }

        function escapeHtml(s) {
            const div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        function handleCellClick(dateStr, hour, half, dayIdx) {
            if (!currentRoomId) {
                Swal.fire('提示', '请先选择会议室', 'warning');
                return;
            }
            const room = rooms.find(r => r.id == currentRoomId);
            const startDt = new Date(`${dateStr}T${String(hour).padStart(2, '0')}:${half === 0 ? '00' : '30'}:00`);
            const endDt = new Date(startDt.getTime() + 30 * 60 * 1000);

            document.getElementById('roomInfoText').innerHTML = 
                `<strong>${room.name}</strong> · ${room.floor} · 容纳${room.capacity}人 · 
                ${room.equipment_list.length > 0 ? room.equipment_list.map(e => `<span class="equipment-tag-sm">${e}</span>`).join('') : '无特殊设备'}`;
            document.getElementById('b_room_id').value = room.id;
            document.getElementById('b_start').value = formatDateTime(startDt);
            document.getElementById('b_end').value = formatDateTime(endDt);
            document.getElementById('b_subject').value = '';
            document.getElementById('b_attendees').value = '';
            document.getElementById('b_booker').value = '';

            const minDt = new Date();
            minDt.setMinutes(0, 0, 0);
            document.getElementById('b_start').min = formatDateTime(minDt).slice(0, 10) + 'T08:00';
            document.getElementById('b_end').min = formatDateTime(minDt).slice(0, 10) + 'T08:00';

            bookingModal.show();
        }

        async function submitBooking() {
            const roomId = parseInt(document.getElementById('b_room_id').value);
            const subject = document.getElementById('b_subject').value.trim();
            const attendees = parseInt(document.getElementById('b_attendees').value);
            const startVal = document.getElementById('b_start').value;
            const endVal = document.getElementById('b_end').value;
            const booker = document.getElementById('b_booker').value.trim();

            if (!subject || !attendees || !startVal || !endVal || !booker) {
                Swal.fire('提示', '请填写完整的预约信息', 'warning');
                return;
            }
            if (attendees <= 0) {
                Swal.fire('提示', '参会人数必须大于0', 'warning');
                return;
            }

            const startTime = startVal.replace('T', ' ') + ':00';
            const endTime = endVal.replace('T', ' ') + ':00';

            const res = await fetch('meeting_api.php?action=book', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ room_id: roomId, subject, attendees, start_time: startTime, end_time: endTime, booker })
            });
            const data = await res.json();
            if (data.code === 200) {
                bookingModal.hide();
                Swal.fire('成功', '预约成功！', 'success');
                loadWeeklyBookings();
            } else {
                Swal.fire({
                    icon: data.code === 409 ? 'warning' : 'error',
                    title: data.code === 409 ? '时间冲突' : '预约失败',
                    text: data.message,
                    confirmButtonText: '知道了'
                });
            }
        }

        async function showDetail(id) {
            const res = await fetch(`meeting_api.php?action=booking_detail&id=${id}`);
            const data = await res.json();
            if (data.code !== 200) {
                Swal.fire('错误', '获取详情失败', 'error');
                return;
            }
            const b = data.data;
            currentDetailId = id;
            const startD = new Date(b.start_time.replace(' ', 'T'));
            const endD = new Date(b.end_time.replace(' ', 'T'));

            document.getElementById('detailContent').innerHTML = `
                <div class="mb-3 p-3 bg-light rounded">
                    <h6 class="fw-bold mb-2 text-primary">${escapeHtml(b.subject)}</h6>
                    <div class="row g-2 text-sm">
                        <div class="col-6"><small class="text-muted">会议室</small><div class="fw-bold">${escapeHtml(b.room_name)}</div></div>
                        <div class="col-6"><small class="text-muted">位置</small><div class="fw-bold">${escapeHtml(b.floor)}</div></div>
                        <div class="col-6"><small class="text-muted">日期</small><div class="fw-bold">${formatDate(startD)}</div></div>
                        <div class="col-6"><small class="text-muted">时间</small><div class="fw-bold">${formatTime(b.start_time)} - ${formatTime(b.end_time)}</div></div>
                        <div class="col-6"><small class="text-muted">参会人数</small><div class="fw-bold">${b.attendees} / ${b.capacity} 人</div></div>
                        <div class="col-6"><small class="text-muted">预订人</small><div class="fw-bold">${escapeHtml(b.booker)}</div></div>
                    </div>
                </div>
                <div>
                    <small class="text-muted d-block mb-1">会议室设备：</small>
                    ${b.equipment_list.length > 0 
                        ? b.equipment_list.map(e => `<span class="equipment-tag-sm">${e}</span>`).join('') 
                        : '<small class="text-muted">未配置特殊设备</small>'}
                </div>
                <div class="mt-3"><small class="text-muted">创建时间：${b.create_time}</small></div>
            `;

            document.getElementById('cancelBookingBtn').onclick = () => cancelBooking(id);
            detailModal.show();
        }

        async function cancelBooking(id) {
            Swal.fire({
                title: '确认取消预约？',
                text: '取消后该时段将释放供其他人预约',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '确认取消',
                cancelButtonText: '返回',
                confirmButtonColor: '#dc3545'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const res = await fetch('meeting_api.php?action=cancel', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const data = await res.json();
                    if (data.code === 200) {
                        detailModal.hide();
                        Swal.fire('成功', '预约已取消', 'success');
                        loadWeeklyBookings();
                    } else {
                        Swal.fire('失败', data.message, 'error');
                    }
                }
            });
        }

        initWeek();
        loadRooms();
    </script>
</body>
</html>
