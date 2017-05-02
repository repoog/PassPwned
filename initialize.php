<?php
require('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("ERROR: Connection failed with " . $conn->connect_error);
}

// Prepare to create site index table.
$table_name = DB_PREFIX . 'site_index';
$table_sql = <<<EOT
CREATE TABLE {$table_name} (
  s_id int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '站点ID',
  table_name varchar(50) NOT NULL COMMENT '站点表名',
  site_name varchar(20) NOT NULL COMMENT '站点名称',
  site_url varchar(30) DEFAULT NULL COMMENT '站点链接',
  site_info varchar(100) DEFAULT NULL COMMENT '站点信息',
  data_amount bigint(15) NOT NULL DEFAULT '0' COMMENT '数据量'
)
EOT;

if ($conn->query($table_sql) === TRUE) {
    echo "SUCCESS: site_index created.\n";
}else {
    echo "ERROR: Creating table with " . $conn->error . "\n";
}

// Prepare to create site items table.
$table_name = DB_PREFIX . 'site_item';
$table_sql = <<<EOT
CREATE TABLE {$table_name} (
  s_id int(10) NOT NULL COMMENT '站点ID',
  username_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户名字段',
  password_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '密文密码字段',
  email_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱字段',
  salt_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '盐值字段',
  ip_item tinyint(1) NOT NULL DEFAULT '0' COMMENT 'IP字段',
  question_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '问题字段',
  answer_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '回答字段',
  plainpass_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '明文密码字段',
  mobile_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '手机字段',
  realname_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '真实姓名字段',
  sex_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别字段',
  qq_item tinyint(1) NOT NULL DEFAULT '0' COMMENT 'QQ字段',
  birthday_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '生日字段',
  idcard_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '身份证字段',
  address_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '住址字段',
  university_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '大学字段',
  education_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '学历字段',
  company_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '公司字段',
  post_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '职位字段',
  phone_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '座机字段',
  account_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '账户字段',
  nickname_item tinyint(1) NOT NULL DEFAULT '0' COMMENT '昵称字段'
)
EOT;

if ($conn->query($table_sql) === TRUE) {
    echo "SUCCESS: site_index created.\n";
}else {
    echo "ERROR: Creating table with " . $conn->error . "\n";
}

// Prepare to create API calling table.
$table_name = DB_PREFIX . 'api_call';
$table_sql = <<<EOT
CREATE TABLE {$table_name} (
  id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  count bigint(10) NOT NULL
)
EOT;

if ($conn->query($table_sql) === TRUE) {
    echo "SUCCESS: site_index created.\n";
}else {
    echo "ERROR: Creating table with " . $conn->error . "\n";
}

$conn->close();
