<?php
require_once '../func.php';
check_login();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>会议室管理 - GovCore 管理中心</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .equipment-tag {
            display: inline-block;
            background: #e3f2fd;
            color: #1565c0;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin: 2px;
        }
        .room-card {
            transition: all 0.2s ease;
        }
        .room-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-dark bg-gov-blue shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">GovCore 管理中心</a>
            <span class="navbar-text text-white">
                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_user']); ?> | <a href="logout.php" class="text-white-50 text-decoration-none">退出</a>
            </span>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar py-4 border-end bg-white" style="min-height: calc(100vh - 56px);">
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-speedometer2 me-2"></i>控制台
                    </a>
                    <a href="emergency.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>应急事件
                    </a>
                    <a href="net_tool.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-broadcast me-2"></i>网络检测工具
                    </a>
                    <a href="upload.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-upload me-2"></i>政策文件上传
                    </a>
                    <a href="meeting_rooms.php" class="list-group-item list-group-item-action active">
                        <i class="bi bi-door-open me-2"></i>会议室管理
                    </a>
                    <a href="budget.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cash-stack me-2"></i>预决算管理
                    </a>
                    <a href="mail.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-envelope-open me-2"></i>意见信箱
                    </a>
                    <a href="mail_keywords.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-exclamation me-2"></i>敏感词管理
                    </a>
                    <a href="opinion_dashboard.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-radar me-2"></i>舆情监测看板
                    </a>
                    <a href="weather_config.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cloud-sun me-2"></i>气象数据源
                    </a>
                </div>
            </div>

            <div class="col-md-10 py-4 bg-light">
                <div class="container-fluid">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-white p-3 rounded shadow-sm">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">控制台</a></li>
                            <li class="breadcrumb-item active fw-bold text-gov-blue">会议室管理</li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-gov-blue fw-bold">
                            <i class="bi bi-door-open me-2"></i>会议室基础信息维护
                        </h5>
                        <button class="btn btn-gov-blue px-4" onclick="openRoomModal()">
                            <i class="bi bi-plus-lg me-2"></i>新增会议室
                        </button>
                    </div>

                    <div id="roomList" class="row g-4">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="roomModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gov-blue text-white">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-plus-circle me-2"></i>新增会议室</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="roomId" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">会议室名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roomName" placeholder="如：第一会议室">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">容纳人数 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="roomCapacity" min="1" placeholder="如：20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">所在楼层 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="roomFloor" placeholder="如：3楼">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">设备清单</label>
                            <div class="d-flex flex-wrap gap-2 mb-2" id="equipmentCheckboxes">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="投影仪" id="eq_projector">
                                    <label class="form-check-label" for="eq_projector">投影仪</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="白板" id="eq_whiteboard">
                                    <label class="form-check-label" for="eq_whiteboard">白板</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="音响系统" id="eq_audio">
                                    <label class="form-check-label" for="eq_audio">音响系统</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="视频会议系统" id="eq_video">
                                    <label class="form-check-label" for="eq_video">视频会议系统</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="电视屏幕" id="eq_tv">
                                    <label class="form-check-label" for="eq_tv">电视屏幕</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="空调" id="eq_ac">
                                    <label class="form-check-label" for="eq_ac">空调</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="专业音响" id="eq_proaudio">
                                    <label class="form-check-label" for="eq_proaudio">专业音响</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="舞台灯光" id="eq_lights">
                                    <label class="form-check-label" for="eq_lights">舞台灯光</label>
                                </div>
                            </div>
                            <input type="text" class="form-control" id="customEquipment" placeholder="其他设备（逗号分隔）">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">状态</label>
                            <select class="form-select" id="roomStatus">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">取消</button>
                    <button type="button" class="btn btn-gov-blue px-4" onclick="saveRoom()">
                        <i class="bi bi-save me-2"></i>保存
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const roomModal = new bootstrap.Modal(document.getElementById('roomModal'));
        let editingId = null;

        async function loadRooms() {
            const res = await fetch('../meeting_api.php?action=rooms');
            const data = await res.json();
            const container = document.getElementById('roomList');
            if (data.code !== 200) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-danger">加载失败</div></div>';
                return;
            }
            if (data.data.length === 0) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-info text-center py-4">暂无会议室，点击"新增会议室"按钮添加</div></div>';
                return;
            }
            container.innerHTML = data.data.map(room => `
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 room-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="fw-bold text-gov-blue mb-1">
                                        <i class="bi bi-door-open me-2"></i>${room.name}
                                        ${room.status == 0 ? '<span class="badge bg-secondary ms-2">已禁用</span>' : ''}
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>${room.floor}
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-people me-1"></i>容纳 ${room.capacity} 人
                                    </small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="editRoom(${room.id})">
                                            <i class="bi bi-pencil me-2"></i>编辑
                                        </a></li>
                                        <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteRoom(${room.id}, '${room.name}')">
                                            <i class="bi bi-trash me-2"></i>删除
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                            <div>
                                <small class="text-muted d-block mb-1">设备配置：</small>
                                ${room.equipment_list.length > 0 
                                    ? room.equipment_list.map(e => `<span class="equipment-tag">${e}</span>`).join('')
                                    : '<small class="text-muted">未配置设备</small>'
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function openRoomModal() {
            editingId = null;
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>新增会议室';
            document.getElementById('roomId').value = '';
            document.getElementById('roomName').value = '';
            document.getElementById('roomCapacity').value = '';
            document.getElementById('roomFloor').value = '';
            document.getElementById('customEquipment').value = '';
            document.getElementById('roomStatus').value = '1';
            document.querySelectorAll('#equipmentCheckboxes input[type="checkbox"]').forEach(cb => cb.checked = false);
            roomModal.show();
        }

        async function editRoom(id) {
            const res = await fetch(`../meeting_api.php?action=rooms&id=${id}`);
            const data = await res.json();
            if (data.code !== 200) {
                Swal.fire('错误', '获取会议室信息失败', 'error');
                return;
            }
            const room = data.data;
            editingId = id;
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>编辑会议室';
            document.getElementById('roomId').value = id;
            document.getElementById('roomName').value = room.name;
            document.getElementById('roomCapacity').value = room.capacity;
            document.getElementById('roomFloor').value = room.floor;
            document.getElementById('roomStatus').value = room.status;

            const equipmentSet = new Set(room.equipment_list);
            const commonEquipments = ['投影仪', '白板', '音响系统', '视频会议系统', '电视屏幕', '空调', '专业音响', '舞台灯光'];
            document.querySelectorAll('#equipmentCheckboxes input[type="checkbox"]').forEach(cb => {
                cb.checked = equipmentSet.has(cb.value);
                if (cb.checked) equipmentSet.delete(cb.value);
            });
            document.getElementById('customEquipment').value = [...equipmentSet].join(',');
            roomModal.show();
        }

        async function saveRoom() {
            const name = document.getElementById('roomName').value.trim();
            const capacity = parseInt(document.getElementById('roomCapacity').value);
            const floor = document.getElementById('roomFloor').value.trim();
            const status = parseInt(document.getElementById('roomStatus').value);

            if (!name || !capacity || !floor) {
                Swal.fire('提示', '请填写完整信息（名称、容量、楼层）', 'warning');
                return;
            }

            const equipment = [];
            document.querySelectorAll('#equipmentCheckboxes input[type="checkbox"]:checked').forEach(cb => {
                equipment.push(cb.value);
            });
            const custom = document.getElementById('customEquipment').value.trim();
            if (custom) {
                custom.split(/[,，]/).map(s => s.trim()).filter(s => s).forEach(s => equipment.push(s));
            }

            const payload = { name, capacity, floor, equipment, status };
            let url = '../meeting_api.php?action=rooms';
            let method = 'POST';
            if (editingId) {
                payload.id = editingId;
                method = 'PUT';
            }

            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.code === 200) {
                Swal.fire('成功', data.message, 'success');
                roomModal.hide();
                loadRooms();
            } else {
                Swal.fire('失败', data.message, 'error');
            }
        }

        function deleteRoom(id, name) {
            Swal.fire({
                title: '确认删除',
                html: `确定要删除会议室 <strong>"${name}"</strong> 吗？<br>该会议室的所有预约记录也将被删除。`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '确定删除',
                cancelButtonText: '取消',
                confirmButtonColor: '#dc3545'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const res = await fetch(`../meeting_api.php?action=rooms&id=${id}`, { method: 'DELETE' });
                    const data = await res.json();
                    if (data.code === 200) {
                        Swal.fire('成功', '删除成功', 'success');
                        loadRooms();
                    } else {
                        Swal.fire('失败', data.message, 'error');
                    }
                }
            });
        }

        loadRooms();
    </script>
</body>
</html>
