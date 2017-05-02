## 功能描述
PassPwned 是一个用于查询社工库的API接口，该接口基于MySQL数据库并使用PHP开发，接口包括：
* 查询社工库站点/数据量/接口调用统计；
* 查询基于关键词的站点信息；
* 查询基于关键词的数据详细信息；
接口返回采用Json格式，用于同域站点下的接口调用和访问。

## 接口设计
PassPwned 基于MySQL数据库设计，采用统一的表前缀，表类型分为站点数据表和基础表。
其中site_index、site_item、api_call为默认基础表，表说明如下：
* site_index：站点信息及表信息索引表；
* site_item：站点数据表字段记录表；
* api_call：接口调用统计表；
接口实现依赖于基础表的构建，否则会造成接口查询错误。

## 接口配置
config.php 为接口配置文件，其中可配置的内容包括：
* 数据库连接信息
* 表前缀信息（默认为sod_）
* 基础表表名信息

## 设计思路
PassPwned 基于统一字段（即所有分表同类型字段字段名统一）和分表设计，并使用基础表作为索引查询依据，即查询时基于参数类型从基础表site_item中确认数据来源以及数据表名，继而查询同类型下所有数据表信息。

若有新数据源添加，仅需在导入数据后自定义分表表名，添加site_index记录并增加site_item记录说明字段是否存在即可（新的字段亦需增加表字段）。

## 基础表结构
在接口配置文件config.php配置之后，可使用`php -f initialize.php`创建以下基础表。

### site_index
<pre>
CREATE TABLE `sod_site_index` (
  s_id int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '站点ID',
  table_name varchar(50) NOT NULL COMMENT '站点表名',
  site_name varchar(20) NOT NULL COMMENT '站点名称',
  site_url varchar(30) DEFAULT NULL COMMENT '站点链接',
  site_info varchar(100) DEFAULT NULL COMMENT '站点信息',
  data_amount bigint(15) NOT NULL DEFAULT '0' COMMENT '数据量'
)
</pre>

### site_item
<pre>
CREATE TABLE `sod_site_item` (
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
</pre>

### api_call
<pre>
CREATE TABLE `sod_api_call` (
  id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  count bigint(10) NOT NULL
)
</pre>
