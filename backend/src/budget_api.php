<?php
require_once 'func.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'years':
        getYears();
        break;
    case 'list':
        getList();
        break;
    case 'export':
        exportData();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function getYears() {
    global $conn;

    $currentYear = intval(date('Y'));
    $years = [];
    for ($i = 0; $i < 5; $i++) {
        $years[] = $currentYear - $i;
    }

    $availableYears = [];
    foreach ($years as $y) {
        $sql = "SELECT COUNT(*) as cnt FROM department_budget WHERE `year` = $y";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            if (intval($row['cnt']) > 0) {
                $availableYears[] = $y;
            }
        }
    }

    if (empty($availableYears)) {
        $availableYears = $years;
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => $availableYears
    ]);
}

function getList() {
    global $conn;

    $year = intval($_GET['year'] ?? date('Y'));
    $keyword = trim($_GET['keyword'] ?? '');
    $sort_field = $_GET['sort_field'] ?? '';
    $sort_order = strtoupper($_GET['sort_order'] ?? 'ASC');

    $allowed_sort = ['department', 'budget_income', 'budget_expenditure', 'final_income', 'final_expenditure'];
    $allowed_order = ['ASC', 'DESC'];

    if (!in_array($sort_field, $allowed_sort)) {
        $sort_field = '';
    }
    if (!in_array($sort_order, $allowed_order)) {
        $sort_order = 'ASC';
    }

    $where = ["`year` = $year"];
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, escape_like($keyword));
        $where[] = "department LIKE '%$keyword_safe%'";
    }
    $where_sql = ' WHERE ' . implode(' AND ', $where);

    $order_sql = ' ORDER BY id ASC';
    if (!empty($sort_field)) {
        $order_sql = " ORDER BY $sort_field $sort_order";
    }

    $sql = "SELECT * FROM department_budget $where_sql $order_sql";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bi = floatval($row['budget_income']);
            $be = floatval($row['budget_expenditure']);
            $fi = floatval($row['final_income']);
            $fe = floatval($row['final_expenditure']);

            $row['diff_rate'] = null;
            if ($be > 0 && $fe > 0) {
                $row['diff_rate'] = round(abs($fe - $be) / $be * 100, 2);
            }

            $list[] = $row;
        }
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'list' => $list,
            'year' => $year,
            'total' => count($list)
        ]
    ]);
}

function exportData() {
    global $conn;

    $year = intval($_GET['year'] ?? date('Y'));
    $format = $_GET['format'] ?? 'csv';

    $sql = "SELECT * FROM department_budget WHERE `year` = $year ORDER BY id ASC";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $list[] = $row;
        }
    }

    $filename = "部门预决算_{$year}";

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['部门名称', '预算收入(万元)', '预算支出(万元)', '决算收入(万元)', '决算支出(万元)', '差额率(%)']);

        foreach ($list as $row) {
            $be = floatval($row['budget_expenditure']);
            $fe = floatval($row['final_expenditure']);
            $diffRate = '';
            if ($be > 0 && $fe > 0) {
                $diffRate = round(abs($fe - $be) / $be * 100, 2) . '%';
            }
            fputcsv($output, [
                $row['department'],
                $row['budget_income'],
                $row['budget_expenditure'],
                $row['final_income'],
                $row['final_expenditure'],
                $diffRate
            ]);
        }

        fclose($output);
    } else {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename . '.xls');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
        echo '<body><table border="1">';
        echo '<tr><th>部门名称</th><th>预算收入(万元)</th><th>预算支出(万元)</th><th>决算收入(万元)</th><th>决算支出(万元)</th><th>差额率(%)</th></tr>';

        foreach ($list as $row) {
            $be = floatval($row['budget_expenditure']);
            $fe = floatval($row['final_expenditure']);
            $diffRate = '-';
            if ($be > 0 && $fe > 0) {
                $diffRate = round(abs($fe - $be) / $be * 100, 2);
            }
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['department']) . '</td>';
            echo '<td>' . $row['budget_income'] . '</td>';
            echo '<td>' . $row['budget_expenditure'] . '</td>';
            echo '<td>' . $row['final_income'] . '</td>';
            echo '<td>' . $row['final_expenditure'] . '</td>';
            echo '<td>' . $diffRate . '</td>';
            echo '</tr>';
        }

        echo '</table></body></html>';
    }
    exit;
}
