<?php
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/location/main.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/url_query/main.php'); 	// URL request var builder.
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/session.php');			// Session class.
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php'); 	// Database class.
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/record_navigation/main.php');	// Record navigation.
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/sorting/main.php'); 		// record sorting.
	require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/cache/main.php'); 		// Page cache.
	
	require_once(__DIR__.'/access/main.php');
	require_once(__DIR__.'/navigation.php');
	require_once(__DIR__.'/data_main.php');
		
	// Replace default session handler.
	$session_handler = new class_session();
	session_set_save_handler($session_handler, TRUE);
		
?>