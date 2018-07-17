<?php

	abstract class APPLICATION_SETTINGS
	{
		const VERSION 			= 0.1;
		const NAME				= 'EHS Training';
		const DIRECTORY_PRIME	= '/apps/rocky';
		const TIME_FORMAT		= 'Y-m-d H:i:s';
	}

	abstract class DATABASE
	{
		const NAME	= 'ehs_training';
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