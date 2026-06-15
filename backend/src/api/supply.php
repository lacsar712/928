<?php
require_once __DIR__ . '/../func.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';

if ($action === 'stock_query') {
    stockQuery();
} else {
    echo json_encode(['code' => 400, 'msg' => '无效的操作']);
}

function stockQuery() {
    global $conn;

    $keyword = trim($_GET['keyword'] ?? '');

    if (empty($keyword)) {
        echo json_encode(['code' => 400, 'msg' => '请输入物资名称']);
        return;
    }

    $keyword_safe = mysqli_real_escape_string($conn, $keyword);

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
