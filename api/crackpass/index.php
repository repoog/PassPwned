<?php
/**
 * Crackable password API
 * @remark: only used in PassPwned without external purpose.
 */

/** Sets up PassPwned configuration file. */
require_once(dirname(dirname(dirname(__FILE__))) . '/pp-config.php');

/** Include md5 cracker API class. */
require_once(ABSPATH . PPINC . '/pp-cracker.php');

$password = addslashes(isset($_GET["password"]) ? trim($_GET["password"]) : '');

// Avoid warning about creating default object from empty value.
if (!isset($strength)) 
    $strength = new stdClass();
// Initialize crack jsonp data to NULL
$strength->crack = null;

// Query md5 password with md5cracker.net API.
$crack = new md5cracker();
$md5_result = $crack->crack($password);

if ($md5_result === 'no') {
	$strength->crack = false;
}else {
	$strength->crack = true;
}

$json = json_encode($strength);
echo $json;
?>