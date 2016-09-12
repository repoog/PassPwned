<?php
/**
 * Leak account API
 * @remark: default used to query account leak situation for normal customers.
 */

include(dirname(dirname(dirname(__FILE__))) . '/include/json.php');

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
$json_obj->set_success();
$index = 0;
foreach ($result_set as $site) {
    // Convert site information to json format.
    $json_obj->set_item("site[" . $index . "]['info']", $site[0]);

    // Convert data item name to json format.
    $item = array();
    $item_index = 0;
    foreach ($site[1][0] as $key => $value) {
        if ($key != 'id') {
            $item[$item_index] = $key;
            $item_index++;
        }
    }
    $json_obj->set_item("site[" . $index . "]['data']", $item);
    $index++;
}

$json_obj->output();