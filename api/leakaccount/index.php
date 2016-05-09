<?php
/**
 * Leak account API
 * @remark: default used to query account leak situation for normal customers.
 */
include_once(dirname(dirname(dirname(__FILE__))) . '/pp-setting.php');

$callback = htmlspecialchars(isset($_GET['callback']) ? trim($_GET['callback']) : '');
$account = addslashes(isset($_GET["account"]) ? trim($_GET["account"]) : '');

// Avoid warning about creating default object from empty value.
if (!isset($risk)) 
    $risk = new stdClass();
$site_num = 0;	// Leak information site index.
$risk->leak = 0;
$risk->site = array();

$field_array = array();

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

/* Verify account type is email or username and generate sql to query relatived tables.
 * @remark:account type verification is only for god mode.
 */
if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.email_item = 1";
	$type_condition = "WHERE email = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
}else {
	// Query username by username field.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.username_item = 1";
	$type_condition = "WHERE username = %s";
	$field_array[0]['table_sql'] = $table_sql;
	$field_array[0]['type_condition'] = $type_condition;
	
	// Query username by nickname field.
	$table_sql = "SELECT b.s_id, b.table_name, b.site_name, b.site_url, b.site_info 
				FROM sod_site_item a, sod_site_index b WHERE a.s_id = b.s_id AND a.nickname_item = 1";
	$type_condition = "WHERE nickname = %s";
	$field_array[1]['table_sql'] = $table_sql;
	$field_array[1]['type_condition'] = $type_condition;
}

function info_query($ppdb, $table_sql, $type_condition, $account) {
	global $site_num;
	// Query existence leak information about email or username.
	$table_array = $ppdb->query($table_sql, true);
	foreach ($table_array as $table_obj) {
		// Check account information existence.
		$info_sql = "SELECT 1 FROM `" . $table_obj->table_name . "` " . $type_condition;
		$query_count = $ppdb->query($ppdb->prepare($info_sql, $account));
	
		if ($query_count > 0) {
			$risk->leak = 1;
			$site_array['name'] = $table_obj->site_name;
			$site_array['url'] = $table_obj->site_url;
			$site_array['info'] = $table_obj->site_info;
			$risk->site[$site_num] = $site_array;
			$site_num++;
		}
	}
	return $risk;
}

// Leep field array to query all information in each filed type.
for ($i=0; $i<count($field_array); $i++) {
	$risk_json = info_query($ppdb, $field_array[$i]['table_sql'], $field_array[$i]['type_condition'], $account);
	if ($risk_json != null) {
		$risk = $risk_json;
	}
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