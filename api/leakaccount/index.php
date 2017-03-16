<?php
/**
 * Leak account API
 * @remark: default used to query account leak situation for normal customers.
 */

include(dirname(dirname(dirname(__FILE__))) . '/include/json.php');
include(dirname(dirname(dirname(__FILE__))) . '/include/api.php');

$account = addslashes(isset($_GET["account"]) ? trim($_GET["account"]) : '');

$json_obj = new JSON();

// Verify account is empty or not.
if ($account === '') {
    $json_obj->output();
	exit(0);
}

// Get searching account result set.
$api_obj = new API();
$result_set = $api_obj->search($account);
$index = 0;
$site_set = array();
foreach ($result_set as $site) {
    // Convert data item name to json format.
    $item = array();
    $item_index = 0;
    foreach ($site[1][0] as $key => $value) {
        if ($key != 'id') {
            $item[$item_index] = $key;
            $item_index++;
        }
    }
    $site_set[$index] = array('info' => $site[0], 'data' => $item);
    $index++;
}
$json_obj->set_item("site", $site_set);
if (!empty($site_set)) {
    $json_obj->set_success();
}

$json_obj->output();