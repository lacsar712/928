-- Database init script for GovCore CMS
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE govcore;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'admin', 'admin888'); -- Password is plaintext for easier CTF demo, or use MD5: 7fef6171469e80d32c0559f88b377245

-- ----------------------------
-- Table structure for news
-- ----------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `publish_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of news
-- ----------------------------
INSERT INTO `news` (`title`, `content`) VALUES 
('XX市人民政府关于印发“十四五”数字政府建设规划的通知', '为深入贯彻落实国家和省关于加强数字政府建设的部署要求，加快推动我市数字政府改革建设，并在全市范围内进行推广实施...'),
('我市召开网络安全和信息化工作会议', '近日，我市召开网络安全和信息化工作会议，深入学习贯彻习近平总书记关于网络强国的重要思想，传达学习全国、全省网络安全和信息化工作会议精神...'),
('关于开展2024年度政务公开评估工作的公告', '为进一步提升全市政务公开工作水平，根据《中华人民共和国政府信息公开条例》规定，决定在全市范围内开展2024年度政务公开第三方评估工作...'),
('市大数据中心成功举办第三届数字城市论坛', '由市大数据中心主办的第三届数字城市论坛在市会议中心隆重举行。来自全国各地的专家学者、企业代表齐聚一堂，共话数字城市建设新未来...'),
('关于防范电信网络诈骗的紧急预警', '近期，我市电信网络诈骗案件高发，不法分子冒充公检法、领导、熟人等实施诈骗。市反诈中心提醒广大市民，务必提高警惕，不轻信、不转账...');

-- ----------------------------
-- Table structure for sys_logs
-- ----------------------------
DROP TABLE IF EXISTS `sys_logs`;
CREATE TABLE `sys_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `payload` text,
  `log_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for emergency_events
-- ----------------------------
DROP TABLE IF EXISTS `emergency_events`;
CREATE TABLE `emergency_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_no` varchar(32) NOT NULL COMMENT '事件编号',
  `event_type` varchar(20) NOT NULL COMMENT '事件类型：自然灾害/事故灾难/公共卫生/社会安全',
  `severity` tinyint(1) NOT NULL COMMENT '严重等级：1-Ⅰ级 2-Ⅱ级 3-Ⅲ级 4-Ⅳ级',
  `occur_time` datetime NOT NULL COMMENT '发生时间',
  `location` varchar(255) NOT NULL COMMENT '地点描述',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT '经度',
  `latitude` decimal(10,7) DEFAULT NULL COMMENT '纬度',
  `description` text NOT NULL COMMENT '现场描述',
  `images` varchar(1000) DEFAULT NULL COMMENT '图片路径，逗号分隔',
  `is_anonymous` tinyint(1) DEFAULT 0 COMMENT '是否匿名：0-否 1-是',
  `reporter_name` varchar(50) DEFAULT NULL COMMENT '上报人姓名',
  `reporter_phone` varchar(20) DEFAULT NULL COMMENT '上报人电话',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：1-待处理 2-已派单 3-已处置 4-已归档',
  `ip_address` varchar(50) DEFAULT NULL COMMENT '上报IP',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_no` (`event_no`),
  KEY `severity` (`severity`),
  KEY `status` (`status`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='突发事件应急上报';

-- ----------------------------
-- Table structure for opinion_keywords
-- ----------------------------
DROP TABLE IF EXISTS `opinion_keywords`;
CREATE TABLE `opinion_keywords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL COMMENT '关键词',
  `sentiment` tinyint(1) NOT NULL DEFAULT 0 COMMENT '情感标签：1-正向 2-中性 3-负向',
  `weight` int(11) DEFAULT 1 COMMENT '权重',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `sentiment` (`sentiment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='舆情监测关键词';

-- ----------------------------
-- Records of opinion_keywords
-- ----------------------------
INSERT INTO `opinion_keywords` (`keyword`, `sentiment`, `weight`) VALUES
('满意度提升', 1, 2),
('便民服务', 1, 1),
('高效办理', 1, 1),
('点赞', 1, 2),
('服务态度好', 1, 2),
('效率低', 3, 2),
('投诉', 3, 3),
('不作为', 3, 3),
('乱收费', 3, 3),
('态度恶劣', 3, 2),
('咨询', 2, 1),
('建议', 2, 1),
('反馈', 2, 1),
('疑问', 2, 1),
('流程', 2, 1);

-- ----------------------------
-- Table structure for opinion_data
-- ----------------------------
DROP TABLE IF EXISTS `opinion_data`;
CREATE TABLE `opinion_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '舆情标题',
  `content` text COMMENT '舆情内容',
  `source_platform` varchar(50) DEFAULT NULL COMMENT '来源平台',
  `sentiment` tinyint(1) DEFAULT 2 COMMENT '情感倾向：1-正向 2-中性 3-负向',
  `matched_keywords` varchar(500) DEFAULT NULL COMMENT '命中关键词，JSON格式',
  `publish_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '发布时间',
  `crawl_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '抓取时间',
  `author` varchar(100) DEFAULT NULL COMMENT '发布作者',
  `url` varchar(500) DEFAULT NULL COMMENT '原文链接',
  PRIMARY KEY (`id`),
  KEY `sentiment` (`sentiment`),
  KEY `source_platform` (`source_platform`),
  KEY `publish_time` (`publish_time`),
  KEY `crawl_time` (`crawl_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='舆情监测数据';

-- ----------------------------
-- Table structure for weather_cache
-- ----------------------------
DROP TABLE IF EXISTS `weather_cache`;
CREATE TABLE `weather_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cache_key` varchar(100) NOT NULL COMMENT '缓存键：today/forecast/aqi',
  `cache_data` mediumtext NOT NULL COMMENT 'JSON 缓存数据',
  `seed` int(11) NOT NULL DEFAULT 0 COMMENT '随机种子，5 分钟内固定',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cache_key` (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='气象数据缓存';

-- ----------------------------
-- Table structure for weather_config
-- ----------------------------
DROP TABLE IF EXISTS `weather_config`;
CREATE TABLE `weather_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL COMMENT '配置键',
  `config_value` varchar(500) NOT NULL COMMENT '配置值',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='气象数据源配置';

-- ----------------------------
-- Records of weather_config
-- ----------------------------
INSERT INTO `weather_config` (`config_key`, `config_value`) VALUES
('data_source', 'mock'),
('mock_url', ''),
('real_url', '');

-- ----------------------------
-- Table structure for meeting_rooms
-- ----------------------------
DROP TABLE IF EXISTS `meeting_rooms`;
CREATE TABLE `meeting_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '会议室名称',
  `capacity` int(11) NOT NULL COMMENT '容纳人数',
  `floor` varchar(50) NOT NULL COMMENT '所在楼层',
  `equipment` text COMMENT '设备清单，逗号分隔',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：1-启用 0-禁用',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会议室基础信息';

-- ----------------------------
-- Records of meeting_rooms
-- ----------------------------
INSERT INTO `meeting_rooms` (`name`, `capacity`, `floor`, `equipment`, `status`) VALUES
('第一会议室', 20, '3楼', '投影仪,白板,视频会议系统,空调', 1),
('第二会议室', 50, '5楼', '投影仪,白板,音响系统,空调,视频会议系统', 1),
('小会议室A', 8, '2楼', '白板,电视屏幕,空调', 1),
('多功能厅', 100, '1楼', '投影仪,专业音响,舞台灯光,空调,视频会议系统', 1),
('洽谈室', 6, '3楼', '白板,电视屏幕,空调', 1);

-- ----------------------------
-- Table structure for meeting_bookings
-- ----------------------------
DROP TABLE IF EXISTS `meeting_bookings`;
CREATE TABLE `meeting_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL COMMENT '会议室ID',
  `subject` varchar(200) NOT NULL COMMENT '会议主题',
  `attendees` int(11) NOT NULL COMMENT '参会人数',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NOT NULL COMMENT '结束时间',
  `booker` varchar(50) NOT NULL COMMENT '预订人',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：1-已预约 0-已取消',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会议室预约记录';

-- ----------------------------
-- Table structure for mail_messages
-- ----------------------------
DROP TABLE IF EXISTS `mail_messages`;
CREATE TABLE `mail_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_no` varchar(32) NOT NULL COMMENT '留言编号',
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `email` varchar(100) NOT NULL COMMENT '邮箱',
  `subject` varchar(255) NOT NULL COMMENT '主题',
  `content` text NOT NULL COMMENT '留言内容',
  `is_public` tinyint(1) DEFAULT 1 COMMENT '是否公开：0-否 1-是',
  `status` tinyint(1) DEFAULT 0 COMMENT '审核状态：0-待审 1-已通过 2-已拒绝',
  `reply_content` text COMMENT '官方回复内容',
  `reply_time` datetime DEFAULT NULL COMMENT '回复时间',
  `reply_admin` varchar(50) DEFAULT NULL COMMENT '回复管理员',
  `ip_address` varchar(50) DEFAULT NULL COMMENT '提交IP',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_no` (`message_no`),
  KEY `status` (`status`),
  KEY `is_public` (`is_public`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='意见信箱留言';

-- ----------------------------
-- Table structure for mail_sensitive_words
-- ----------------------------
DROP TABLE IF EXISTS `mail_sensitive_words`;
CREATE TABLE `mail_sensitive_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(100) NOT NULL COMMENT '敏感词',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `word` (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='意见信箱敏感词';

-- ----------------------------
-- Records of mail_sensitive_words
-- ----------------------------
INSERT INTO `mail_sensitive_words` (`word`) VALUES
('暴力'),
('色情'),
('赌博'),
('毒品'),
('反动'),
('邪教'),
('诈骗'),
('贪污'),
('腐败'),
('上访');

-- ----------------------------
-- Table structure for leaders
-- ----------------------------
DROP TABLE IF EXISTS `leaders`;
CREATE TABLE `leaders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '姓名',
  `position` varchar(100) NOT NULL COMMENT '职务',
  `department` varchar(20) NOT NULL COMMENT '所属部门：市委/市政府/人大/政协',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像路径',
  `responsibility` varchar(500) DEFAULT NULL COMMENT '分管领域',
  `bio` text COMMENT '简介',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序权重',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `department` (`department`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='领导干部信息公示';

-- ----------------------------
-- Records of leaders
-- ----------------------------
INSERT INTO `leaders` (`name`, `position`, `department`, `avatar`, `responsibility`, `bio`, `email`, `sort_order`) VALUES
('张明', '市委书记', '市委', NULL, '主持市委全面工作，分管党的建设、干部人事工作', '男，汉族，1968年5月生，研究生学历，中共党员，现任XX市委书记。', 'zhangming@govcore.cn', 1),
('李军', '市委副书记', '市委', NULL, '协助书记处理市委日常工作，分管政法、信访维稳', '男，汉族，1972年8月生，大学学历，中共党员，现任XX市委副书记。', 'lijun@govcore.cn', 2),
('王芳', '市委常委、组织部部长', '市委', NULL, '主持市委组织部工作，分管组织、人才、老干部工作', '女，汉族，1975年3月生，研究生学历，中共党员，现任XX市委常委、组织部部长。', 'wangfang@govcore.cn', 3),
('赵强', '市委常委、宣传部部长', '市委', NULL, '主持市委宣传部工作，分管宣传思想、意识形态、文化建设', '男，汉族，1970年12月生，大学学历，中共党员，现任XX市委常委、宣传部部长。', 'zhaoqiang@govcore.cn', 4),
('刘伟', '市委常委、纪委书记', '市委', NULL, '主持市纪委监委工作，分管纪检监察、党风廉政建设', '男，汉族，1973年6月生，研究生学历，中共党员，现任XX市委常委、纪委书记、监委主任。', 'liuwei@govcore.cn', 5),

('陈红', '市长', '市政府', NULL, '主持市政府全面工作，分管财政、审计工作', '女，汉族，1969年10月生，研究生学历，中共党员，现任XX市委副书记、市长。', 'chenhong@govcore.cn', 1),
('孙涛', '常务副市长', '市政府', NULL, '协助市长处理市政府日常工作，分管发改、统计、自然资源', '男，汉族，1971年4月生，大学学历，中共党员，现任XX市委常委、常务副市长。', 'suntao@govcore.cn', 2),
('周明', '副市长', '市政府', NULL, '分管工业经济、科技、商务、市场监管', '男，汉族，1974年9月生，研究生学历，中共党员，现任XX市人民政府副市长。', 'zhouming@govcore.cn', 3),
('吴丽', '副市长', '市政府', NULL, '分管教育、卫生健康、文化旅游、体育', '女，汉族，1976年2月生，大学学历，中共党员，现任XX市人民政府副市长。', 'wuli@govcore.cn', 4),
('郑华', '副市长、公安局局长', '市政府', NULL, '主持市公安局工作，分管公安、司法、应急管理', '男，汉族，1968年11月生，研究生学历，中共党员，现任XX市人民政府副市长、市公安局局长。', 'zhenghua@govcore.cn', 5),

('黄磊', '市人大常委会主任', '人大', NULL, '主持市人大常委会全面工作，分管办公室', '男，汉族，1965年7月生，研究生学历，中共党员，现任XX市人大常委会主任、党组书记。', 'huanglei@govcore.cn', 1),
('徐峰', '市人大常委会副主任', '人大', NULL, '分管财政经济委员会、预算工作委员会', '男，汉族，1967年1月生，大学学历，中共党员，现任XX市人大常委会副主任。', 'xufeng@govcore.cn', 2),
('林霞', '市人大常委会副主任', '人大', NULL, '分管法制委员会、监察和司法委员会', '女，汉族，1970年5月生，研究生学历，中共党员，现任XX市人大常委会副主任。', 'linxia@govcore.cn', 3),

('杨建华', '市政协主席', '政协', NULL, '主持市政协全面工作，分管办公室', '男，汉族，1964年8月生，大学学历，中共党员，现任XX市政协主席、党组书记。', 'yangjianhua@govcore.cn', 1),
('范晓燕', '市政协副主席', '政协', NULL, '分管提案委员会、教科文卫体委员会', '女，汉族，1969年3月生，研究生学历，民盟盟员，现任XX市政协副主席。', 'fanxiaoyan@govcore.cn', 2),
('马建国', '市政协副主席', '政协', NULL, '分管经济委员会、农业和农村委员会', '男，汉族，1966年12月生，大学学历，中共党员，现任XX市政协副主席。', 'majianguo@govcore.cn', 3);

-- ----------------------------
-- Table structure for department_budget
-- ----------------------------
DROP TABLE IF EXISTS `department_budget`;
CREATE TABLE `department_budget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL COMMENT '年度',
  `department` varchar(100) NOT NULL COMMENT '部门名称',
  `budget_income` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '预算收入',
  `budget_expenditure` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '预算支出',
  `final_income` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '决算收入',
  `final_expenditure` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '决算支出',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `year` (`year`),
  KEY `department` (`department`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门预决算公开';

-- ----------------------------
-- Records of department_budget
-- ----------------------------
INSERT INTO `department_budget` (`year`, `department`, `budget_income`, `budget_expenditure`, `final_income`, `final_expenditure`) VALUES
(2022, '市教育局', 125000.00, 118000.00, 123000.00, 119500.00),
(2022, '市公安局', 280000.00, 265000.00, 285000.00, 271000.00),
(2022, '市财政局', 95000.00, 88000.00, 92000.00, 89500.00),
(2022, '市卫健委', 150000.00, 142000.00, 155000.00, 148000.00),
(2022, '市住建局', 200000.00, 190000.00, 198000.00, 195000.00),
(2022, '市交通运输局', 110000.00, 105000.00, 112000.00, 108000.00),
(2022, '市人社局', 88000.00, 82000.00, 86000.00, 83500.00),
(2022, '市自然资源局', 76000.00, 70000.00, 74000.00, 72500.00),
(2023, '市教育局', 130000.00, 122000.00, 128000.00, 125000.00),
(2023, '市公安局', 295000.00, 278000.00, 300000.00, 285000.00),
(2023, '市财政局', 98000.00, 91000.00, 96000.00, 93500.00),
(2023, '市卫健委', 158000.00, 149000.00, 162000.00, 155000.00),
(2023, '市住建局', 210000.00, 198000.00, 205000.00, 202000.00),
(2023, '市交通运输局', 115000.00, 108000.00, 118000.00, 112000.00),
(2023, '市人社局', 92000.00, 86000.00, 90000.00, 88500.00),
(2023, '市自然资源局', 80000.00, 74000.00, 78000.00, 77000.00),
(2024, '市教育局', 135000.00, 128000.00, 133000.00, 130000.00),
(2024, '市公安局', 310000.00, 292000.00, 320000.00, 305000.00),
(2024, '市财政局', 102000.00, 95000.00, 100000.00, 98000.00),
(2024, '市卫健委', 165000.00, 156000.00, 170000.00, 162000.00),
(2024, '市住建局', 220000.00, 208000.00, 215000.00, 212000.00),
(2024, '市交通运输局', 120000.00, 112000.00, 122000.00, 116000.00),
(2024, '市人社局', 96000.00, 89000.00, 94000.00, 92000.00),
(2024, '市自然资源局', 85000.00, 78000.00, 82000.00, 81000.00),
(2025, '市教育局', 140000.00, 132000.00, 0.00, 0.00),
(2025, '市公安局', 320000.00, 300000.00, 0.00, 0.00),
(2025, '市财政局', 105000.00, 98000.00, 0.00, 0.00),
(2025, '市卫健委', 170000.00, 160000.00, 0.00, 0.00),
(2025, '市住建局', 230000.00, 215000.00, 0.00, 0.00),
(2025, '市交通运输局', 125000.00, 118000.00, 0.00, 0.00),
(2025, '市人社局', 100000.00, 93000.00, 0.00, 0.00),
(2025, '市自然资源局', 88000.00, 82000.00, 0.00, 0.00),
(2026, '市教育局', 148000.00, 138000.00, 0.00, 0.00),
(2026, '市公安局', 335000.00, 315000.00, 0.00, 0.00),
(2026, '市财政局', 110000.00, 102000.00, 0.00, 0.00),
(2026, '市卫健委', 178000.00, 168000.00, 0.00, 0.00),
(2026, '市住建局', 240000.00, 225000.00, 0.00, 0.00),
(2026, '市交通运输局', 130000.00, 122000.00, 0.00, 0.00),
(2026, '市人社局', 105000.00, 97000.00, 0.00, 0.00),
(2026, '市自然资源局', 92000.00, 86000.00, 0.00, 0.00);

SET FOREIGN_KEY_CHECKS = 1;
