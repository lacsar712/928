<?php
require_once '../func.php';
check_login();

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'keyword_list':
        getKeywordList();
        break;
    case 'keyword_add':
        addKeyword();
        break;
    case 'keyword_update':
        updateKeyword();
        break;
    case 'keyword_delete':
        deleteKeyword();
        break;
    case 'opinion_list':
        getOpinionList();
        break;
    case 'stats':
        getStats();
        break;
    case 'generate':
        generateOpinionData();
        break;
    default:
        echo json_encode(['code' => 400, 'msg' => '无效的操作']);
        break;
}

function getKeywordList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 50);
    $sentiment = intval($_GET['sentiment'] ?? 0);
    $keyword = trim($_GET['keyword'] ?? '');

    $where = [];
    if ($sentiment > 0) {
        $where[] = "sentiment = $sentiment";
    }
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "keyword LIKE '%$keyword_safe%'";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM opinion_keywords $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT * FROM opinion_keywords $where_sql ORDER BY weight DESC, create_time DESC LIMIT $offset, $page_size";
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

function addKeyword() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $keyword = trim($input['keyword'] ?? '');
    $sentiment = intval($input['sentiment'] ?? 2);
    $weight = intval($input['weight'] ?? 1);

    if (empty($keyword)) {
        echo json_encode(['code' => 400, 'msg' => '关键词不能为空']);
        return;
    }

    if ($sentiment < 1 || $sentiment > 3) {
        echo json_encode(['code' => 400, 'msg' => '情感标签不正确']);
        return;
    }

    $keyword_safe = mysqli_real_escape_string($conn, $keyword);

    $check_sql = "SELECT id FROM opinion_keywords WHERE keyword = '$keyword_safe'";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['code' => 400, 'msg' => '该关键词已存在']);
        return;
    }

    $sql = "INSERT INTO opinion_keywords (keyword, sentiment, weight) VALUES ('$keyword_safe', $sentiment, $weight)";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('KeywordAdd', "Keyword: $keyword, Sentiment: $sentiment");
        echo json_encode(['code' => 200, 'msg' => '添加成功', 'data' => ['id' => mysqli_insert_id($conn)]]);
    } else {
        echo json_encode(['code' => 500, 'msg' => '添加失败：' . mysqli_error($conn)]);
    }
}

function updateKeyword() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $keyword = trim($input['keyword'] ?? '');
    $sentiment = intval($input['sentiment'] ?? 2);
    $weight = intval($input['weight'] ?? 1);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    if (empty($keyword)) {
        echo json_encode(['code' => 400, 'msg' => '关键词不能为空']);
        return;
    }

    if ($sentiment < 1 || $sentiment > 3) {
        echo json_encode(['code' => 400, 'msg' => '情感标签不正确']);
        return;
    }

    $keyword_safe = mysqli_real_escape_string($conn, $keyword);

    $check_sql = "SELECT id FROM opinion_keywords WHERE keyword = '$keyword_safe' AND id != $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['code' => 400, 'msg' => '该关键词已存在']);
        return;
    }

    $sql = "UPDATE opinion_keywords SET keyword = '$keyword_safe', sentiment = $sentiment, weight = $weight WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('KeywordUpdate', "ID: $id, Keyword: $keyword");
        echo json_encode(['code' => 200, 'msg' => '更新成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '更新失败：' . mysqli_error($conn)]);
    }
}

function deleteKeyword() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['code' => 405, 'msg' => '请求方法不允许']);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'msg' => 'ID不正确']);
        return;
    }

    $sql = "DELETE FROM opinion_keywords WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        Logger::logAction('KeywordDelete', "ID: $id");
        echo json_encode(['code' => 200, 'msg' => '删除成功']);
    } else {
        echo json_encode(['code' => 500, 'msg' => '删除失败：' . mysqli_error($conn)]);
    }
}

function getOpinionList() {
    global $conn;

    $page = intval($_GET['page'] ?? 1);
    $page_size = intval($_GET['page_size'] ?? 20);
    $sentiment = intval($_GET['sentiment'] ?? 0);
    $source = trim($_GET['source'] ?? '');
    $keyword = trim($_GET['keyword'] ?? '');

    $where = [];
    if ($sentiment > 0) {
        $where[] = "sentiment = $sentiment";
    }
    if (!empty($source)) {
        $source_safe = mysqli_real_escape_string($conn, $source);
        $where[] = "source_platform = '$source_safe'";
    }
    if (!empty($keyword)) {
        $keyword_safe = mysqli_real_escape_string($conn, $keyword);
        $where[] = "(title LIKE '%$keyword_safe%' OR content LIKE '%$keyword_safe%')";
    }

    $where_sql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

    $count_sql = "SELECT COUNT(*) as total FROM opinion_data $where_sql";
    $count_result = mysqli_query($conn, $count_sql);
    $total = 0;
    if ($count_row = mysqli_fetch_assoc($count_result)) {
        $total = intval($count_row['total']);
    }

    $offset = ($page - 1) * $page_size;
    $sql = "SELECT * FROM opinion_data $where_sql ORDER BY publish_time DESC LIMIT $offset, $page_size";
    $result = mysqli_query($conn, $sql);

    $list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['matched_keywords'])) {
                $row['matched_keywords_arr'] = json_decode($row['matched_keywords'], true);
            } else {
                $row['matched_keywords_arr'] = [];
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

function getStats() {
    global $conn;

    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');

    $today_count_sql = "SELECT COUNT(*) as cnt FROM opinion_data WHERE crawl_time >= '$today_start' AND crawl_time <= '$today_end'";
    $today_result = mysqli_query($conn, $today_count_sql);
    $today_count = 0;
    if ($row = mysqli_fetch_assoc($today_result)) {
        $today_count = intval($row['cnt']);
    }

    $total_count_sql = "SELECT COUNT(*) as cnt FROM opinion_data WHERE crawl_time >= '$today_start' AND crawl_time <= '$today_end'";
    $total_result = mysqli_query($conn, $total_count_sql);
    $total_count = 0;
    if ($row = mysqli_fetch_assoc($total_result)) {
        $total_count = intval($row['cnt']);
    }

    $negative_count_sql = "SELECT COUNT(*) as cnt FROM opinion_data WHERE crawl_time >= '$today_start' AND crawl_time <= '$today_end' AND sentiment = 3";
    $negative_result = mysqli_query($conn, $negative_count_sql);
    $negative_count = 0;
    if ($row = mysqli_fetch_assoc($negative_result)) {
        $negative_count = intval($row['cnt']);
    }

    $negative_ratio = $total_count > 0 ? round(($negative_count / $total_count) * 100, 1) : 0;

    $matched_keywords_sql = "SELECT matched_keywords FROM opinion_data WHERE crawl_time >= '$today_start' AND crawl_time <= '$today_end' AND matched_keywords IS NOT NULL";
    $matched_result = mysqli_query($conn, $matched_keywords_sql);
    $matched_count = 0;
    if ($matched_result) {
        while ($row = mysqli_fetch_assoc($matched_result)) {
            $keywords = json_decode($row['matched_keywords'], true);
            if (is_array($keywords)) {
                $matched_count += count($keywords);
            }
        }
    }

    $sentiment_sql = "SELECT sentiment, COUNT(*) as cnt FROM opinion_data GROUP BY sentiment";
    $sentiment_result = mysqli_query($conn, $sentiment_sql);
    $sentiment_data = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
    if ($sentiment_result) {
        while ($row = mysqli_fetch_assoc($sentiment_result)) {
            if ($row['sentiment'] == 1) {
                $sentiment_data['positive'] = intval($row['cnt']);
            } elseif ($row['sentiment'] == 2) {
                $sentiment_data['neutral'] = intval($row['cnt']);
            } elseif ($row['sentiment'] == 3) {
                $sentiment_data['negative'] = intval($row['cnt']);
            }
        }
    }

    $platform_sql = "SELECT source_platform, COUNT(*) as cnt FROM opinion_data GROUP BY source_platform ORDER BY cnt DESC LIMIT 10";
    $platform_result = mysqli_query($conn, $platform_sql);
    $platform_data = [];
    if ($platform_result) {
        while ($row = mysqli_fetch_assoc($platform_result)) {
            $platform_data[] = [
                'platform' => $row['source_platform'] ?: '未知',
                'count' => intval($row['cnt'])
            ];
        }
    }

    echo json_encode([
        'code' => 200,
        'msg' => 'success',
        'data' => [
            'today_count' => $today_count,
            'negative_ratio' => $negative_ratio,
            'matched_keywords_count' => $matched_count,
            'sentiment' => $sentiment_data,
            'platforms' => $platform_data
        ]
    ]);
}

function generateOpinionData() {
    global $conn;

    $platforms = ['微博', '微信公众号', '抖音', '快手', 'B站', '知乎', '小红书', '今日头条', '百度贴吧', '豆瓣'];
    $positive_titles = [
        '市民点赞政务服务效率大幅提升',
        '新政策落地，群众满意度创新高',
        '社区便民服务中心正式启用',
        '政务APP上线，办事更便捷',
        '市民表扬窗口工作人员服务态度好',
        '绿色通道解民忧，群众送锦旗',
        '办事效率显著提高，群众交口称赞',
        '便民举措暖人心，市民纷纷点赞',
        '政务公开透明，获市民好评',
        '一站式服务让群众少跑腿'
    ];
    $neutral_titles = [
        '关于办理社保业务的流程咨询',
        '市民咨询公积金提取相关问题',
        '建议优化政务大厅停车秩序',
        '关于营业执照年检的疑问',
        '市民反馈办事指引需进一步完善',
        '咨询不动产登记办理时间',
        '建议增加网上办事功能',
        '关于医保报销比例的咨询',
        '市民建议优化APP用户体验',
        '咨询子女入学相关政策'
    ];
    $negative_titles = [
        '市民投诉办事窗口效率低下',
        '群众反映工作人员态度恶劣',
        '举报某部门存在乱收费现象',
        '市民质疑政务公开不及时',
        '投诉热线无人接听，群众不满',
        '办理业务多次跑，群众抱怨',
        '市民反映不作为问题亟待解决',
        '投诉办事流程繁琐不合理',
        '群众质疑政策执行不到位',
        '窗口排队时间过长引不满'
    ];
    $positive_contents = [
        '今天去办理业务，工作人员非常热情，办事效率也很高，不到半小时就办完了，给你们点赞！',
        '新推出的便民服务真的太方便了，足不出户就能办好，感谢政府的贴心服务。',
        '窗口的小李同志服务态度特别好，耐心解答我的问题，帮我解决了大难题。',
        '政务服务中心的环境很好，办事效率也高，现在办事真的越来越方便了。',
        '为政府的高效服务点赞，真正做到了为人民服务，让群众满意。'
    ];
    $neutral_contents = [
        '想咨询一下社保转移需要什么材料，打了几次电话没人接，希望能有人回复一下。',
        '请问公积金贷款的额度是怎么计算的？我在官网上没找到具体说明。',
        '建议政务大厅能增加一些引导标识，第一次去不太好找办事窗口。',
        '营业执照年检网上申报的系统有点复杂，希望能出个详细的操作指南。',
        '想了解一下今年的学区划分情况，什么时候会公布相关信息？'
    ];
    $negative_contents = [
        '办个业务跑了三趟还没办好，每次都说缺材料，就不能一次性说清楚吗？效率太低了！',
        '窗口工作人员态度恶劣，问个问题不耐烦，这样的服务态度怎么让群众满意？',
        '明明公示说免费办理，到了窗口却要收服务费，这不是乱收费吗？',
        '投诉电话打了几十个都没人接，这就是所谓的便民服务热线？',
        '办事流程太繁琐了，要填十几张表，能不能简化一下？太不人性化了！'
    ];
    $authors = [
        '阳光市民', '热心网友', '城市观察者', '普通群众', '办事市民',
        '小明同学', '老王说事', '城市之声', '民生观察员', '百姓代言人'
    ];

    $keywords_sql = "SELECT * FROM opinion_keywords";
    $keywords_result = mysqli_query($conn, $keywords_sql);
    $all_keywords = [];
    if ($keywords_result) {
        while ($row = mysqli_fetch_assoc($keywords_result)) {
            $all_keywords[] = $row;
        }
    }

    $count = rand(3, 8);
    $inserted = 0;

    for ($i = 0; $i < $count; $i++) {
        $sentiment_rand = mt_rand(1, 100);
        if ($sentiment_rand <= 30) {
            $sentiment = 1;
            $title_pool = $positive_titles;
            $content_pool = $positive_contents;
        } elseif ($sentiment_rand <= 70) {
            $sentiment = 2;
            $title_pool = $neutral_titles;
            $content_pool = $neutral_contents;
        } else {
            $sentiment = 3;
            $title_pool = $negative_titles;
            $content_pool = $negative_contents;
        }

        $title = $title_pool[array_rand($title_pool)];
        $content = $content_pool[array_rand($content_pool)];
        $platform = $platforms[array_rand($platforms)];
        $author = $authors[array_rand($authors)];

        $matched = [];
        foreach ($all_keywords as $kw) {
            if (mb_strpos($title, $kw['keyword']) !== false || mb_strpos($content, $kw['keyword']) !== false) {
                $matched[] = [
                    'keyword' => $kw['keyword'],
                    'sentiment' => $kw['sentiment'],
                    'weight' => $kw['weight']
                ];
            }
        }

        if (empty($matched)) {
            $num_matched = rand(0, min(2, count($all_keywords)));
            if ($num_matched > 0) {
                $random_keys = array_rand($all_keywords, $num_matched);
                if (!is_array($random_keys)) {
                    $random_keys = [$random_keys];
                }
                foreach ($random_keys as $rk) {
                    $matched[] = [
                        'keyword' => $all_keywords[$rk]['keyword'],
                        'sentiment' => $all_keywords[$rk]['sentiment'],
                        'weight' => $all_keywords[$rk]['weight']
                    ];
                }
            }
        }

        $matched_json = !empty($matched) ? json_encode($matched, JSON_UNESCAPED_UNICODE) : null;

        $hours_offset = rand(-24, 0);
        $minutes_offset = rand(-59, 0);
        $publish_time = date('Y-m-d H:i:s', time() + $hours_offset * 3600 + $minutes_offset * 60);

        $title_safe = mysqli_real_escape_string($conn, $title);
        $content_safe = mysqli_real_escape_string($conn, $content);
        $platform_safe = mysqli_real_escape_string($conn, $platform);
        $author_safe = mysqli_real_escape_string($conn, $author);
        $matched_safe = $matched_json ? mysqli_real_escape_string($conn, $matched_json) : null;

        $sql = "INSERT INTO opinion_data (title, content, source_platform, sentiment, matched_keywords, publish_time, author) 
                VALUES ('$title_safe', '$content_safe', '$platform_safe', $sentiment, " . ($matched_safe ? "'$matched_safe'" : 'NULL') . ", '$publish_time', '$author_safe')";

        if (mysqli_query($conn, $sql)) {
            $inserted++;
        }
    }

    Logger::logAction('OpinionGenerate', "Generated $inserted opinion records");

    echo json_encode([
        'code' => 200,
        'msg' => "成功生成 $inserted 条舆情数据",
        'data' => ['count' => $inserted]
    ]);
}
