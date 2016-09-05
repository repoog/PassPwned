<?php
/**
 * Leak account API
 * @remark: default used to query account leak situation for normal customers.
 */

include_once(dirname(dirname(dirname(__FILE__))) . '/include/api.php');

$account = addslashes(isset($_GET["account"]) ? trim($_GET["account"]) : '');

// Avoid warning about creating default object with empty value.
if (!isset($api)) 
    $api = new stdClass();
$api->state = "Fail";

// Verify account is empty or not.
if ($account === '') {
	$json = json_encode($api);
    echo $json;
	exit(0);
}

// Get searching account result set.
$api_obj = new API();
$result_set = $api_obj->search($account);
$api->state = "Success";
$index = 0;
foreach ($result_set as $site) {
    // Convert site information to json format.
    $api->site[$index]['info'] = $site[0];

    // Convert data item name to json format.
    $item = array();
    $item_index = 0;
    foreach ($site[1][0] as $key => $value) {
        if ($key != 'id') {
            $item[$item_index] = $key;
            $item_index++;
        }
    }
    $api->site[$index]['data'] = $item;
    $index++;
}

$json = json_encode($api);
echo $json;