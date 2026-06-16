<?php
require_once '../func.php';
check_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'years':
        getYears();
        break;
    case 'list':
        getList();
        break;
    case 'add':
        addRecord();
        break;
    case 'update':
        updateRecord();
        break;
    case 'delete':
        deleteRecord();
        break;
    case 'import':
        importExcel();
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
    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 50);

    $where = ["`year` = $year"];
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "department LIKE '%$keyword_safe%'";
    }
    $where_sql = ' WHERE ' . implode(' AND ', $where);

    $count_sql = "SELECT COUNT(*) as total FROM department_budget $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT * FROM department_budget $where_sql ORDER BY id ASC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $be = floatval($row['budget_expenditure']);
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
            'total' => $total,
            'page' => $page,
            'page_size' => $page_size,
            'total_pages' => ceil($total / $page_size)
        ]
    ]);
}

function addRecord() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $year = intval($input['year'] ?? 0);
    $department = trim($input['department'] ?? '');
    $budget_income = floatval($input['budget_income'] ?? 0);
    $budget_expenditure = floatval($input['budget_expenditure'] ?? 0);
    $final_income = floatval($input['final_income'] ?? 0);
    $final_expenditure = floatval($input['final_expenditure'] ?? 0);

    if ($year <= 0 || empty($department)) {
        echo json_encode(['code' => 400, 'msg' => '年度和部门名称不能为空']);
        return;
    }

    $department_safe = mysqli_real_escape_string($conn, $department);

    $check_sql = "SELECT id FROM department_budget WHERE `year` = $year AND department = '$department_safe'";
    $check_result = mysqli_query($conn, $check_sql);
    if ($check_result && mysqli_fetch_assoc($check_result)) {
        echo json_encode(['code' => 400, 'msg' => '该年度已存在相同部门记录']);
        return;
    }

    $sql = "INSERT INTO department_budget (`year`, department, budget_income, budget_expenditure, final_income, final_expenditure) VALUES ($year, '$department_safe', $budget_income, $budget_expenditure, $final_income, $final_expenditure)";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('BudgetAdd', "Year: $year, Dept: $department");
        echo json_encode(['code' => 200, 'msg' => '新增成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '新增失败：' . mysqli_error($conn)]);
    }
}

function updateRecord() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $id = intval($input['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '记录ID不正确']);
        return;
    }

    $fields = [];
    if (isset($input['year'])) {
        $fields[] = "`year` = " . intval($input['year']);
    }
    if (isset($input['department'])) {
        $dept_safe = mysqli_real_escape_string($conn, trim($input['department']));
        $fields[] = "department = '$dept_safe'";
    }
    if (isset($input['budget_income'])) {
        $fields[] = "budget_income = " . floatval($input['budget_income']);
    }
    if (isset($input['budget_expenditure'])) {
        $fields[] = "budget_expenditure = " . floatval($input['budget_expenditure']);
    }
    if (isset($input['final_income'])) {
        $fields[] = "final_income = " . floatval($input['final_income']);
    }
    if (isset($input['final_expenditure'])) {
        $fields[] = "final_expenditure = " . floatval($input['final_expenditure']);
    }

    if (empty($fields)) {
        echo json_encode(['code' => 400, 'msg' => '没有可更新的字段']);
        return;
    }

    $sql = "UPDATE department_budget SET " . implode(', ', $fields) . " WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        Logger::logAction('BudgetUpdate', "ID: $id");
        echo json_encode(['code' => 200, 'msg' => '修改成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '修改失败：' . mysqli_error($conn)]);
    }
}

function deleteRecord() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => '记录ID不正确']);
        return;
    }

    $sql = "DELETE FROM department_budget WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('BudgetDelete', "ID: $id");
        echo json_encode(['code' => 200, 'msg' => '删除成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '删除失败：' . mysqli_error($conn)]);
    }
}

function parseXlsx($filePath) {
    if (!class_exists('ZipArchive')) {
        return [];
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return [];
    }

    $ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    $sharedStrings = [];
    $xmlStrings = $zip->getFromName('xl/sharedStrings.xml');
    if ($xmlStrings !== false) {
        $xml = simplexml_load_string($xmlStrings);
        if ($xml) {
            $children = $xml->children($ns);
            foreach ($children as $si) {
                $siChildren = $si->children($ns);
                if (isset($siChildren->t)) {
                    $sharedStrings[] = (string)$siChildren->t;
                } elseif (isset($siChildren->r)) {
                    $text = '';
                    foreach ($siChildren->r as $rt) {
                        $rtChildren = $rt->children($ns);
                        if (isset($rtChildren->t)) {
                            $text .= (string)$rtChildren->t;
                        }
                    }
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = trim(strip_tags($si->asXML()));
                }
            }
        }
    }

    $rows = [];
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if ($sheetXml === false) {
        $zip->close();
        return [];
    }

    $xml = simplexml_load_string($sheetXml);
    if (!$xml) {
        $zip->close();
        return [];
    }

    $ws = $xml->children($ns);
    if (!isset($ws->sheetData)) {
        $zip->close();
        return [];
    }

    $rowIndex = 0;
    foreach ($ws->sheetData->row as $row) {
        $rowIndex++;
        if ($rowIndex == 1) {
            continue;
        }

        $cells = [];
        $maxCol = 0;
        foreach ($row->c as $cell) {
            $colLetter = preg_replace('/[0-9]/', '', (string)$cell['r']);
            $colIndex = colLetterToIndex($colLetter);
            $cellType = (string)$cell['t'];
            $cellChildren = $cell->children($ns);
            $value = '';

            if ($cellType === 's' && isset($cellChildren->v)) {
                $idx = intval((string)$cellChildren->v);
                if (isset($sharedStrings[$idx])) {
                    $value = $sharedStrings[$idx];
                }
            } elseif ($cellType === 'inlineStr' && isset($cellChildren->is)) {
                $isChildren = $cellChildren->is->children($ns);
                if (isset($isChildren->t)) {
                    $value = (string)$isChildren->t;
                } else {
                    $value = trim(strip_tags($cellChildren->is->asXML()));
                }
            } elseif (isset($cellChildren->v)) {
                $value = (string)$cellChildren->v;
            }

            $cells[$colIndex] = $value;
            if ($colIndex > $maxCol) {
                $maxCol = $colIndex;
            }
        }

        $rowData = [];
        for ($i = 0; $i <= $maxCol; $i++) {
            $rowData[] = $cells[$i] ?? '';
        }

        if (count($rowData) >= 5 && !empty($rowData[0])) {
            $rows[] = $rowData;
        }
    }

    $zip->close();
    return $rows;
}

function colLetterToIndex($letter) {
    $letter = strtoupper($letter);
    $len = strlen($letter);
    $index = 0;
    for ($i = 0; $i < $len; $i++) {
        $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
    }
    return $index - 1;
}

function importExcel() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    if (!isset($_FILES['file'])) {
        echo json_encode(['code' => 400, 'msg' => '请选择上传文件']);
        return;
    }

    $year = intval($_POST['year'] ?? 0);
    if ($year <= 0) {
        echo json_encode(['code' => 400, 'msg' => '请选择导入年度']);
        return;
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
        echo json_encode(['code' => 400, 'msg' => '仅支持 CSV / XLS / XLSX 格式']);
        return;
    }

    $rows = [];
    if ($ext === 'csv') {
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            $header = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 5) {
                    $rows[] = $data;
                }
            }
            fclose($handle);
        }
    } elseif ($ext === 'xlsx') {
        $rows = parseXlsx($file['tmp_name']);
    } elseif ($ext === 'xls') {
        echo json_encode(['code' => 400, 'msg' => '旧版 .xls 格式暂不支持，请另存为 .xlsx 或 CSV 格式后再上传']);
        return;
    }

    if (empty($rows)) {
        echo json_encode(['code' => 400, 'msg' => '文件中无有效数据行']);
        return;
    }

    $success = 0;
    $fail = 0;

    foreach ($rows as $row) {
        $department = mysqli_real_escape_string($conn, trim($row[0]));
        $budget_income = floatval(str_replace(',', '', trim($row[1])));
        $budget_expenditure = floatval(str_replace(',', '', trim($row[2])));
        $final_income = floatval(str_replace(',', '', trim($row[3])));
        $final_expenditure = floatval(str_replace(',', '', trim($row[4])));

        if (empty($department)) {
            $fail++;
            continue;
        }

        $check_sql = "SELECT id FROM department_budget WHERE `year` = $year AND department = '$department'";
        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_fetch_assoc($check_result)) {
            $sql = "UPDATE department_budget SET budget_income = $budget_income, budget_expenditure = $budget_expenditure, final_income = $final_income, final_expenditure = $final_expenditure WHERE `year` = $year AND department = '$department'";
        } else {
            $sql = "INSERT INTO department_budget (`year`, department, budget_income, budget_expenditure, final_income, final_expenditure) VALUES ($year, '$department', $budget_income, $budget_expenditure, $final_income, $final_expenditure)";
        }

        if (mysqli_query($conn, $sql)) {
            $success++;
        } else {
            $fail++;
        }
    }

    Logger::logAction('BudgetImport', "Year: $year, Success: $success, Fail: $fail");
    echo json_encode([
        'code' => 200,
        'msg' => "导入完成：成功 {$success} 条，失败 {$fail} 条",
        'data' => ['success' => $success, 'fail' => $fail]
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
