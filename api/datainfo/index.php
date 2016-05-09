<?php
/**
 * Data amount and site information API
 * @remark: Used to query account leak situation for normal customers.
 */
include_once(dirname(dirname(dirname(__FILE__))) . '/pp-setting.php');

$callback = htmlspecialchars(isset($_GET['callback']) ? trim($_GET['callback']) : '');
$info_type = addslashes(isset($_GET['type']) ? trim($_GET['type']) : '');	// info type is site or data.

$site_num = 0;	// Leak information site index.

// Avoid warning about creating default object from empty value.
if (!isset($info)) 
    $info = new stdClass();
    
if ($info_type === 'site') {
	$info->site_amount = 0;
	$info->data_amount = 0;
	$info->call_amount = 0;
	
	$data_sql = "SELECT site_amount, data_amount, call_amount FROM vw_site_count";
	$data_info = $ppdb->query($data_sql, true);
	$info->site_amount = $data_info[0]->site_amount;
	$info->data_amount = $data_info[0]->data_amount;
	$info->call_amount = $data_info[0]->call_amount;
}elseif ($info_type === 'data') {
	$info->site = array();
	
	$site_sql = "SELECT site_name, site_info, data_amount FROM vw_site_info";
	$site_info_array = $ppdb->query($site_sql, true);
	foreach ($site_info_array as $site_obj) {
		$site_array['site_name'] = $site_obj->site_name;
		$site_array['site_info'] = $site_obj->site_info;
		$site_array['data_amount'] = $site_obj->data_amount;
		$info->site[$site_num] = $site_array;
		$site_num++;
	}
}

$ppdb->close();

$json = json_encode($info);

if ($callback)
{
	echo $callback . '(' . $json . ')';
}else {
	echo $json;
}
?>