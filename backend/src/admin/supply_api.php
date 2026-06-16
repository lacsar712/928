<?php
require_once '../func.php';
check_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'supply_list':
        supplyList();
        break;
    case 'supply_create':
        supplyCreate();
        break;
    case 'supply_update':
        supplyUpdate();
        break;
    case 'supply_delete':
        supplyDelete();
        break;
    case 'warehouse_list':
        warehouseList();
        break;
    case 'warehouse_create':
        warehouseCreate();
        break;
    case 'warehouse_update':
        warehouseUpdate();
        break;
    case 'warehouse_delete':
        warehouseDelete();
        break;
    case 'stock_in':
        stockIn();
        break;
    case 'stock_out':
        stockOut();
        break;
    case 'stock_query':
        stockQuery();
        break;
    case 'stock_logs':
        stockLogs();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function supplyList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);
    $keyword = trim($_GET['keyword'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $warehouse_id = intval($_GET['warehouse_id'] ?? 0);
    $warning = intval($_GET['warning'] ?? 0);

    $where = [];
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, escape_like($keyword));
        $where[] = "si.name LIKE '%$keyword_safe%'";
    }
    if (!empty($category)) {
        $category_safe = mysqli_real_escape_string($conn, $category);
        $where[] = "si.category = '$category_safe'";
    }
    if ($warehouse_id > 0) {
        $where[] = "si.warehouse_id = $warehouse_id";
    }
    if ($warning === 1) {
        $where[] = "si.quantity < si.safety_stock";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM supply_inventory si $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT si.*, sw.name as warehouse_name FROM supply_inventory si LEFT JOIN supply_warehouse sw ON si.warehouse_id = sw.id $where_sql ORDER BY si.create_time DESC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['is_warning'] = intval($row['quantity']) < intval($row['safety_stock']);
            $list[] = $row;
        }
    }

    $warning_count = 0;
    $wc_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM supply_inventory WHERE quantity < safety_stock");
    if ($wc_row = mysqli_fetch_assoc($wc_result)) {
        $warning_count = intval($wc_row['cnt']);
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
            'warning_count' => $warning_count
        ]
    ]);
}

function supplyCreate() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = mysqli_real_escape_string($conn, trim($input['name'] ?? ''));
    $category = mysqli_real_escape_string($conn, trim($input['category'] ?? ''));
    $unit = mysqli_real_escape_string($conn, trim($input['unit'] ?? '件'));
    $quantity = intval($input['quantity'] ?? 0);
    $safety_stock = intval($input['safety_stock'] ?? 0);
    $warehouse_id = intval($input['warehouse_id'] ?? 0);
    $expiry_date = mysqli_real_escape_string($conn, trim($input['expiry_date'] ?? ''));
    $entry_time = mysqli_real_escape_string($conn, trim($input['entry_time'] ?? ''));

    if (empty($name) || $warehouse_id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '物资名称和仓库为必填项']);
        return;
    }

    $expiry_sql = empty($expiry_date) ? 'NULL' : "'$expiry_date'";
    $entry_sql = empty($entry_time) ? 'NULL' : "'$entry_time'";

    $sql = "INSERT INTO supply_inventory (name, category, unit, quantity, safety_stock, warehouse_id, expiry_date, entry_time) VALUES ('$name', '$category', '$unit', $quantity, $safety_stock, $warehouse_id, $expiry_sql, $entry_sql)";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('SupplyCreate', "物资新增: $name, 数量: $quantity");
        echo json_encode(['code' => 200, 'msg' => '物资新增成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '新增失败：' . mysqli_error($conn)]);
    }
}

function supplyUpdate() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $name = mysqli_real_escape_string($conn, trim($input['name'] ?? ''));
    $category = mysqli_real_escape_string($conn, trim($input['category'] ?? ''));
    $unit = mysqli_real_escape_string($conn, trim($input['unit'] ?? '件'));
    $safety_stock = intval($input['safety_stock'] ?? 0);
    $warehouse_id = intval($input['warehouse_id'] ?? 0);
    $expiry_date = mysqli_real_escape_string($conn, trim($input['expiry_date'] ?? ''));

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '物资ID不正确']);
        return;
    }

    $expiry_sql = empty($expiry_date) ? 'NULL' : "'$expiry_date'";

    $sql = "UPDATE supply_inventory SET name='$name', category='$category', unit='$unit', safety_stock=$safety_stock, warehouse_id=$warehouse_id, expiry_date=$expiry_sql WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('SupplyUpdate', "物资更新: ID=$id");
        echo json_encode(['code' => 200, 'msg' => '物资更新成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '更新失败：' . mysqli_error($conn)]);
    }
}

function supplyDelete() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '物资ID不正确']);
        return;
    }

    $sql = "DELETE FROM supply_inventory WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "DELETE FROM supply_stock_log WHERE inventory_id=$id");
        Logger::logAction('SupplyDelete', "物资删除: ID=$id");
        echo json_encode(['code' => 200, 'msg' => '物资删除成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '删除失败：' . mysqli_error($conn)]);
    }
}

function warehouseList() {
    global $conn;

    $sql = "SELECT * FROM supply_warehouse ORDER BY create_time DESC";
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
        'data' => ['list' => $list]
    ]);
}

function warehouseCreate() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = mysqli_real_escape_string($conn, trim($input['name'] ?? ''));
    $address = mysqli_real_escape_string($conn, trim($input['address'] ?? ''));
    $manager = mysqli_real_escape_string($conn, trim($input['manager'] ?? ''));

    if (empty($name)) {
        echo json_encode(['code' => 400, 'msg' => '仓库名称为必填项']);
        return;
    }

    $sql = "INSERT INTO supply_warehouse (name, address, manager) VALUES ('$name', '$address', '$manager')";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('WarehouseCreate', "仓库新增: $name");
        echo json_encode(['code' => 200, 'msg' => '仓库新增成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '新增失败：' . mysqli_error($conn)]);
    }
}

function warehouseUpdate() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $name = mysqli_real_escape_string($conn, trim($input['name'] ?? ''));
    $address = mysqli_real_escape_string($conn, trim($input['address'] ?? ''));
    $manager = mysqli_real_escape_string($conn, trim($input['manager'] ?? ''));

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '仓库ID不正确']);
        return;
    }

    $sql = "UPDATE supply_warehouse SET name='$name', address='$address', manager='$manager' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('WarehouseUpdate', "仓库更新: ID=$id");
        echo json_encode(['code' => 200, 'msg' => '仓库更新成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '更新失败：' . mysqli_error($conn)]);
    }
}

function warehouseDelete() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '仓库ID不正确']);
        return;
    }

    $check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM supply_inventory WHERE warehouse_id=$id");
    $row = mysqli_fetch_assoc($check);
    if (intval($row['cnt']) > 0) {
        echo json_encode(['code' => 400, 'msg' => '该仓库下还有物资，无法删除']);
        return;
    }

    $sql = "DELETE FROM supply_warehouse WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('WarehouseDelete', "仓库删除: ID=$id");
        echo json_encode(['code' => 200, 'msg' => '仓库删除成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '删除失败：' . mysqli_error($conn)]);
    }
}

function stockIn() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $inventory_id = intval($input['inventory_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    $remark = mysqli_real_escape_string($conn, trim($input['remark'] ?? ''));
    $operator = mysqli_real_escape_string($conn, trim($input['operator'] ?? ''));

    if ($inventory_id <= 0 || $quantity <= 0) {
        echo json_encode(['code' => 400, 'msg' => '物资ID和入库数量必须大于0']);
        return;
    }

    $check = mysqli_query($conn, "SELECT id FROM supply_inventory WHERE id=$inventory_id");
    if (!mysqli_fetch_assoc($check)) {
        echo json_encode(['code' => 400, 'msg' => '物资台账记录不存在']);
        return;
    }

    $sql = "INSERT INTO supply_stock_log (inventory_id, type, quantity, remark, operator) VALUES ($inventory_id, 'in', $quantity, '$remark', '$operator')";
    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "UPDATE supply_inventory SET quantity = quantity + $quantity, entry_time = NOW() WHERE id = $inventory_id");
        Logger::logAction('StockIn', "入库: 物资ID=$inventory_id, 数量=$quantity");
        echo json_encode(['code' => 200, 'msg' => '入库登记成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '入库失败：' . mysqli_error($conn)]);
    }
}

function stockOut() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $inventory_id = intval($input['inventory_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 0);
    $remark = mysqli_real_escape_string($conn, trim($input['remark'] ?? ''));
    $operator = mysqli_real_escape_string($conn, trim($input['operator'] ?? ''));

    if ($inventory_id <= 0 || $quantity <= 0) {
        echo json_encode(['code' => 400, 'msg' => '物资ID和出库数量必须大于0']);
        return;
    }

    $check = mysqli_query($conn, "SELECT quantity FROM supply_inventory WHERE id=$inventory_id");
    $row = mysqli_fetch_assoc($check);
    if (!$row) {
        echo json_encode(['code' => 400, 'msg' => '物资台账记录不存在']);
        return;
    }
    if (intval($row['quantity']) < $quantity) {
        echo json_encode(['code' => 400, 'msg' => '库存不足，当前库存：' . $row['quantity']]);
        return;
    }

    $sql = "INSERT INTO supply_stock_log (inventory_id, type, quantity, remark, operator) VALUES ($inventory_id, 'out', $quantity, '$remark', '$operator')";
    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "UPDATE supply_inventory SET quantity = quantity - $quantity WHERE id = $inventory_id");
        Logger::logAction('StockOut', "出库: 物资ID=$inventory_id, 数量=$quantity");
        echo json_encode(['code' => 200, 'msg' => '出库登记成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '出库失败：' . mysqli_error($conn)]);
    }
}

function stockQuery() {
    global $conn;

    $keyword = trim($_GET['keyword'] ?? '');

    if (empty($keyword)) {
        echo json_encode(['code' => 400, 'msg' => '请输入物资名称']);
        return;
    }

    $keyword_safe = mysqli_real_escape_string($conn, escape_like($keyword));

    $sql = "SELECT si.name, si.category, si.unit, si.quantity, si.warehouse_id, sw.name as warehouse_name FROM supply_inventory si LEFT JOIN supply_warehouse sw ON si.warehouse_id = sw.id WHERE si.name LIKE '%$keyword_safe%'";
    $result = mysqli_query($conn, $sql);

    $items = [];
    $total_quantity = 0;
    $name = '';
    $category = '';
    $unit = '';
    $distribution = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $name = $row['name'];
            $category = $row['category'];
            $unit = $row['unit'];
            $qty = intval($row['quantity']);
            $total_quantity += $qty;
            $distribution[] = [
                'warehouse_id' => intval($row['warehouse_id']),
                'warehouse_name' => $row['warehouse_name'],
                'quantity' => $qty
            ];
        }
    }

    if (empty($distribution)) {
        echo json_encode(['code' => 200, 'msg' => '未找到相关物资', 'data' => null]);
        return;
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'name' => $name,
            'category' => $category,
            'unit' => $unit,
            'total_quantity' => $total_quantity,
            'distribution' => $distribution
        ]
    ]);
}

function stockLogs() {
    global $conn;

    $inventory_id = intval($_GET['inventory_id'] ?? 0);
    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);

    $where = [];
    if ($inventory_id > 0) {
        $where[] = "ssl.inventory_id = $inventory_id";
    }
    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM supply_stock_log ssl $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT ssl.*, si.name as supply_name FROM supply_stock_log ssl LEFT JOIN supply_inventory si ON ssl.inventory_id = si.id $where_sql ORDER BY ssl.create_time DESC LIMIT $offset, $page_size";
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
