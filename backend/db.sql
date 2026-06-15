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

SET FOREIGN_KEY_CHECKS = 1;
