<?php
// no need to edit this file, see config.php
ini_set('display_errors', 1);
//error_reporting(1);

// connect
try {
	if ( DB_DRIVER == 'sqlite' ) {
		$db = new PDO( DB_DRIVER.':'.dirname(__FILE__).DIRECTORY_SEPARATOR.DB_NAME );
	}
	else {
		$db = new PDO ( DB_DRIVER.':host='.DB_SERVER.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD );
	}

	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (Exception $e) {
	header( 'HTTP/1.1 503 Service Temporarily Unavailable' );
	if ( defined('DISPLAY_ERRORS') && DISPLAY_ERRORS ) {
		echo '<h1>PDO connection failed:</h1>';
		echo '<pre>';
		echo $e->getMessage();
		print_r( $e );
		echo '</pre>';
	}
	else {
		echo '<h1>Service Currently Unavailable</h1>';
    echo '<p><i>Could not connect to the database</i></p>';
	}
	die();
}

