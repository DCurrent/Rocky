<?php

	abstract class APPLICATION_SETTINGS
	{
		const VERSION 		= '1.0.1';
		const NAME			= 'Rocky';
		const ADMINS		= 'dwhibb0, dvcask2';
		const ADMIN_MAIN 	= 'dvcask2';
		const AUTHENTICATE_URL = 'https://ehs.uky.edu/apps/rocky';
		const DIRECTORY_PRIME	= '/apps/rocky';
		const TIME_FORMAT		= 'Y-m-d H:i:s';
	}
	
	abstract class DATABASE
	{
		const 
			//HOST 		= 'gensql.ad.uky.edu\general',	// Database host (server name or address)
			NAME 		= 'ehs_training';				// Database logical name.
			//USER 		= 'EHSInfo_User',				// User name to access database.
			//PASSWORD	= 'ehsinfo',					// Password to access database.
			//CHARSET	= 'UTF-8';					// Character set.
	}

	abstract class MAILING
	{
		const
			TO		= '',
			CC		= '',
			BCC		= 'dc@caskeys.com',
			SUBJECT = 'Training Alert',
			FROM 	= 'ehs_noreply@uky.edu';
	}	

?>