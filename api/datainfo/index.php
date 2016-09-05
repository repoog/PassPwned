<?php
/**
 * Data amount and site information API
 * @remark: Used to query account leak situation for normal customers.
 */
include(dirname(dirname(dirname(__FILE__))) . '/include/api.php');

// Avoid warning about creating default object with empty value.
if (!isset($api)) 
    $api = new stdClass();
$api->state = "Fail";

// Query site data amount and api call amount
$api_obj = new API();
$amount_set = $api_obj->site_amount();
$api->state = "Success";
$api->amount = $amount_set;

$json = json_encode($api);
echo $json;
