<?php 

// declare(strict_types=1);

namespace dc\nahoni;

abstract class DEFAULTS	
{
	const 	LIFE		= 1440;					// Default session lifetime (seconds).
			
			// Stored procedure default names.
	const	SP_PREFIX	= NULL;							// Prefix for all other SP names.
	const	SP_CLEAN	= 'session_clean';	// SP to remove all expired sessions.
	const	SP_DESTROY	= 'session_destroy';	// SP to remove single session on command.
	const	SP_GET		= 'session_get';		// SP to read session data.
	const	SP_SET		= 'session_set';		// SP to write session data.
}

?>

