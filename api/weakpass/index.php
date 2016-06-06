<?php
/**
 * Weak password API
 * @remark: only used in PassPwned without external purpose.
 */
include_once(dirname(dirname(dirname(__FILE__))) . '/pp-setting.php');

$plainpass = addslashes(isset($_GET["plainpass"]) ? trim($_GET["plainpass"]) : '');

// Avoid warning about creating default object from empty value.
if (!isset($strength)) 
    $strength = new stdClass();
// Initialize strength jsonp data to NULL
$strength->weakness = null;

// Verify plainpass is empty or not.
if ($plainpass === '') {
	$strength->weakness = false;
	$json = json_encode($strength);
	echo $json;
	exit(0);
}

// Query plain password from weak password table
$query_result = $ppdb->query($ppdb->prepare("SELECT plainpass FROM sod_site_weakpass WHERE plainpass = '%s'", $plainpass));
$ppdb->close();

if ($query_result >= 1) {
	$strength->weakness = true;
}elseif ($query_result === 0) {
	$strength->weakness = false;
}

$json = json_encode($strength);
echo $json;
?>