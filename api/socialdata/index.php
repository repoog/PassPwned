<?php
/**
 * Socail data API
 * @remark: check mode post request and client ip to provide god query mode.
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
    // Convert data item to json format.
    $api->site[$index]['data'] = $site[1];
    $index++;
}

$json = json_encode($api);
echo $json;