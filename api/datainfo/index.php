<?php
/**
 * Data amount and site information API
 * @remark: Used to query account leak situation for normal customers.
 */
include(dirname(dirname(dirname(__FILE__))) . '/include/json.php');

$json_obj = new JSON();

// Query site data amount and api call amount
$api_obj = new API();
$amount_set = $api_obj->site_amount();

$json_obj->set_success();
$json_obj->set_item('amount', $amount_set);

$json_obj->output();