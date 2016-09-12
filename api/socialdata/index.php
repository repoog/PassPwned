<?php
/**
 * Socail data API
 * @remark: check mode post request and client ip to provide god query mode.
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
    // Convert data item to json format.
    $json_obj->set_item("site[" . $index . "]['data']", $site[1]);
    $index++;
}

$json_obj->output();