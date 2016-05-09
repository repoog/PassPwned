<?php
/**
 * Used to set up database class file and common vars.
 */

/** Sets up PassPwned configuration file. */
require_once(dirname(__FILE__) . '/pp-config.php');

/** Sets up database class */
require(ABSPATH . PPINC . '/pp-db.php');

/** Load the database class file and instantiate the `$wpdb` global. */
global $ppdb;
if (isset($ppdb))
	return;

/** Create database connection for API. */
$ppdb = new ppdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
?>