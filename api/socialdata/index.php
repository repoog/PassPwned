<?php
/**
 * Socail data API
 * @remark: check mode post request and client ip to provide god query mode.
 */
include_once(dirname(dirname(dirname(__FILE__))) . '/pp-setting.php');

$callback = htmlspecialchars(isset($_GET['callback']) ? trim($_GET['callback']) : '');
$account = addslashes(isset($_GET["account"]) ? trim($_GET["account"]) : '');

$auth_ip = '127.0.0.1';	// Default auth ip of god mode.

// Avoid warning about creating default object from empty value.
if (!isset($risk)) 
    $risk = new stdClass();
$risk->site = array();
$site_num = 0;	// Leak information site index.

// Check post mode and client ip to convert to god query mode.
if ($_SERVER['REMOTE_ADDR'] != $auth_ip) {
	exit(0);
}

// Verify account is empty or not.
if ($account === '') {
	$json = json_encode($risk);

	if ($callback) {
		echo $callback . '(' . $json . ')';
	}else {
		echo $json;
	}
	exit(0);
}

/* Verify account type is mobile, qq, idcard, realname or others in god mode.
 * @remark:account type verification is only for god mode.
 */
if (filter_var($account, FILTER_VALIDATE_EMAIL)) {	// Verify account is email or not.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.email_item = 1";
	$type_condition = "WHERE email = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
}elseif(preg_match("/^1[34578]\d{9}$/", $account)){	// Verify account is mobile or not.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.mobile_item = 1";
	$type_condition = "WHERE mobile = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
}elseif (preg_match("/^\d{5,}$/", $account)) {	// Verify account is qq number or not.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.qq_item = 1";
	$type_condition = "WHERE qq = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
}elseif (preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{4}$/", $account) || 
	preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/", $account)) {	// Verify account is id card or not.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.idcard_item = 1";
	$type_condition = "WHERE idcard = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
}else {	// Verify account is username,nickname or realname.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.username_item = 1";
	$type_condition = "WHERE username = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
	
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.nickname_item = 1";
	$type_condition = "WHERE nickname = %s";
	$field_array[1]['table_sql'] = $table_sql;
	$field_array[1]['type_condition'] = $type_condition;
	
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.realname_item = 1";
	$type_condition = "WHERE realname = %s";
	$field_array[2]['table_sql'] = $table_sql;
	$field_array[2]['type_condition'] = $type_condition;
}

// Query detail leak information about account.
function detail_query($ppdb, $table_sql, $type_condition, $account) {
	global $site_num, $risk;
	$table_array = $ppdb->query($table_sql, true);
	foreach ($table_array as $table_obj) {
		// Query all account detail information.
		$detail_info_sql = "SELECT * FROM `" . $table_obj->table_name . "` " . $type_condition;
		$query_detail = $ppdb->query($ppdb->prepare($detail_info_sql, $account), true);
		
		if ($query_detail != null) {
			// Query all fields of target table in information_schema database.
			$table_column_sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME != 'id' AND table_name = '" . $table_obj->table_name . "'";
			$column_array = $ppdb->query($table_column_sql, true);
			foreach ($column_array as $column_obj) {
				$column_name = $column_obj->COLUMN_NAME;
				$site_array[$column_name] = $query_detail[0]->$column_name;
			}
			$site_array['name'] = $table_obj->site_name;
			$risk->site[$site_num] = $site_array;
			$site_num++;
		}
	}
}

// Leep field array to query all detail information in each filed type.
for ($i=0; $i<count($field_array); $i++) {
	detail_query($ppdb, $field_array[$i]['table_sql'], $field_array[$i]['type_condition'], $account);
}

$ppdb->close();

$json = json_encode($risk);

if ($callback)
{
	echo $callback . '(' . $json . ')';
}else {
	echo $json;
}
?>