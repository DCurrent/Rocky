 <?php

require('interface.php');
//require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/database/main.php');

class rocky_class_access_request
{
	private
		$auth_account 	= NULL,	// POST
		$auth_password	= NULL,	// POST
		$auth_logoff	= NULL,	// GET		
		$auth_login		= NULL; // POST
		
	public function __construct()
	{
		$this->populate();
	}
	
	// Accessors
	public function get_auth_account()
	{
		return $this->auth_account;
	}
	
	public function get_auth_password()
	{
		return $this->auth_password;
	}
	
	public function get_logoff()
	{
		return $this->auth_logoff;
	}
	
	// Actions
	
	// Populate all data members with matching GET key.
	private function populate()
	{
		// Interate through each class variable.
		foreach($this as $key => $value) 
		{			
			// If we can find a matching a post var with key matching
			// key of current object var, set object var to the get value. 
			if(isset($_REQUEST[$key]))
			{					
				$this->$key = $_REQUEST[$key];           						
			}
		}
	}		
}

class rocky_class_access {

	/*
	access
	Damon Vaughn Caskey
	2012-01-09
	
	Account and authentication handler.
	
	TODO: This class is a total mess. It was pretty much thrown 
	together and is nothign close to object oriented. It needs 
	a ground up revamp.	
	*/
	
	private $request		= NULL;	// Request (GET/POST) object.
	
	private	$db_m			= NULL;	// Database object.
		
	private $login_result_m	= NULL; // Result of login attempt.
	private $redirect_url_m = NULL; // Resource user is attempting to access.
	
	private $auth_result_m	= NULL; // Result of login.
	private $dialog_m		= NULL; // Dialog for user.
	
	private $name_f_m		= NULL;	// User's first name.
	private $name_m_m		= NULL;	// User's middle name.
	private $name_l_m		= NULL;	// User's last name.
	private $id_m			= NULL; // User's ID #.
	private $account_m		= NULL;	// User's account name.
	private $credential_m	= NULL; // User's account credential.
	private $email_m		= NULL;	// User's email address
	private $ip_m			= NULL;	// User's IP address.
	
	function __construct()
	{
		/*
		Constructor
		Damon Vaughn Caskey
		2012_01_11
		
		Class constructor.
		*/
		
		if(session_status() == PHP_SESSION_NONE){
			//There is no active session
			session_start();
		}
		
		$this->request = new rocky_class_access_request();		
		
		// Pass session data for client to data members.
		$this->ip_m 		= $_SERVER['REMOTE_ADDR'];
		if(isset($_SESSION[ACCESS_SES_KEY::ACCOUNT])) 	$this->account_m	= $_SESSION[ACCESS_SES_KEY::ACCOUNT];
		if(isset($_SESSION[ACCESS_SES_KEY::NAME_F])) 	$this->name_f_m  	= $_SESSION[ACCESS_SES_KEY::NAME_F];
		if(isset($_SESSION[ACCESS_SES_KEY::NAME_L]))	$this->name_l_m		= $_SESSION[ACCESS_SES_KEY::NAME_L];
		if(isset($_SESSION[ACCESS_SES_KEY::EMAIL]))		$this->email_m 		= $_SESSION[ACCESS_SES_KEY::EMAIL];
	}
	
	function __destruct()
	{
	}		
	
	// Return data member.
	public function get_dialog()
	{
		return $this->dialog_m;
	}
	
	// Return data member.
	public function get_authorization_result()
	{
		return $this->auth_result_m;
	}
	
	// Return data member.
	public function get_name_full()
	{
		return $this->name_f_m .' '.$this->name_l_m;
	}
	
	// Return data member.
	public function get_name_f()
	{
		return $this->name_f_m;
	}
	
	// Return data member.
	public function get_name_l()
	{
		return $this->name_l_m;
	}
	
	// Return data member.
	public function get_ip()
	{
		return $this->ip_m;
	}
	
	// Return data member.
	public function get_account()
	{
		return $this->account_m;
	}
	
	// Return data member.
	public function get_email()
	{
		return $this->email_m;
	}
	
	public function access_verify($cRedir=NULL, $list=NULL)
	{	
		/*
		Damon Vaughn Caskey
		verify
		2011_11_10
		
		Verify user is logged into session with AD account, and is part of authorized group. 
		
		$cRedir:	Should a log in be required, this will appear on log in screen after successful sign in. 
		$list:		Comma delimited List of authorized users. If provided, user's AD account will be verified against list. If NULL, any valid user may access the page.
		*/
		
		$AD_cn 	= NULL;								//AD account name from session.
		$val	= NULL;								//Value extracted from array loop.
		$result = ACCESS_AUTHORIZED_RESULT::NO;		//Authorized?	
		
		//	Locate current user (if any).	
		$AD_cn = $this->account_m;
				
		// Temporary quick fix. It's me!
		$AD_cn = 'dvcask2';
		
		// 	Default for redirect dialog if not set.
		$cRedir = $cRedir ? $cRedir : $_SERVER['PHP_SELF'];
		
		// Verify account is set at all. If so, then evaluate against provided list (if any).			
		if($this->account_m === NULL)											
		{	
			$result	= ACCESS_AUTHORIZED_RESULT::NONE;
		}
		else
		{								
			// If a list of authorized accounts is provided, verify current user is included.
			if(isset($list))							
			{
				// Remove spaces and break into array.				
				$list = str_replace (' ', '', $list);	//Remove spaces.
				$list = explode(',', $list);			//Break into array.				
				
				// Loop array collection.
				foreach($list as $val)				
				{
					// Current element match vs. user account?
					if($val == $this->account_m || $this->account_m == ACCESS_SETTINGS::ADMINISTRATOR)
					{					
						$result = ACCESS_AUTHORIZED_RESULT::YES;						
					}
				}
			}
			else
			{
				$result = ACCESS_AUTHORIZED_RESULT::YES;								
			}		
		}
		
		// Now we'll take action based on authorization result.
		switch ($result)
		{
			// Client is logged in with sufficiant access.
			case ACCESS_AUTHORIZED_RESULT::YES:			
				
				break;
			
			// Client is logged in but lacks sufficiant access.
			case ACCESS_AUTHORIZED_RESULT::NO:				
				
				// Set no access dialog.
				$_SESSION[ACCESS_SES_KEY::DIALOG] = '<span class="text-warning">'."We're sorry ".$this->name_f_m.", but you are not permitted to access this resource. Please log in with an authorized account.".'</span>';				
			
			// No client is logged in.	
			default:
			case ACCESS_AUTHORIZED_RESULT::NONE:
				
				$_SESSION[ACCESS_SES_KEY::REDIRECT] = $cRedir;	
				
				// If headers are not sent, redirect to login page. Otherwise we'll just have
				// to settle for an inline message.
				if(headers_sent())
				{
					echo '<span class="text-danger">'.$_SESSION[ACCESS_SES_KEY::DIALOG].'</span>';
				}
				else
				{			
					header('Location: '.ACCESS_SETTINGS::AUTHENTICATE_URL);
				}				
				
				// Exit the script here. This stops bots that ignore headers and prevents 
				// users from proceeding even if headers had already been sent.
				exit;
				break;			
		}	
		
		// Return result.
		return $result;
	}
	
	public function logoff()
	{			
		session_unset();
		
		if(session_status() === PHP_SESSION_ACTIVE)
		{
			session_destroy();
		}
		
		$this->account_m = NULL;
		$this->email_m 	= NULL;
		$this->id_m 	= NULL;
		$this->ip_m 	= NULL;
		$this->name_f_m	= NULL;
		$this->name_m_m = NULL;
		$this->name_l_m = NULL;
		
		$this->dialog_m = '<span class="text-success">You have successfully logged off.</span>';	
	}
	
	public function login($cAD = array("Host" => ACCESS_SETTINGS::LDAP_HOST, "BaseDn" => ACCESS_SETTINGS::LDAP_BASE_DN), $prefix = array(NULL, "ad/", "ad\\", "mc/", "mc\\"))
	{		
		/*
		login
		Damon Vaughn Caskey
		2012-02-03
		
		Process login attempt.		
		*/
		
		$principal		= NULL;		// Active directory principal (account).
		$result			= NULL;		// Active directory account search result.
		$entries		= NULL;		// Active directory entry array.
		
		$bind			= FALSE;	// Result of bind attempt.
		$ldap			= NULL;		// ldap connection reference.
		
		$db				= NULL;		// Database object.
		$query			= NULL;		// Query object.
		
		$req_account	= NULL;
		$req_credential	= NULL;
				
		// Get values.
		$this->dialog_m			= isset($_SESSION[ACCESS_SES_KEY::DIALOG]) ? $_SESSION[ACCESS_SES_KEY::DIALOG] : NULL;
		$this->redirect_url_m	= isset($_SESSION[ACCESS_SES_KEY::REDIRECT]) ? $_SESSION[ACCESS_SES_KEY::REDIRECT] : NULL;
		$req_account 			= $this->request->get_auth_account();
		$req_credential			= $this->request->get_auth_password();
				
		// If request is to logoff, run logoff function and return.
		if($this->request->get_logoff())
		{
			$this->logoff();
			return;
		}
		
		// User provided credentials? 
		if ($req_account != NULL && $req_credential != NULL)
		{			
			// Check local account? 			
			if(ACCESS_SETTINGS::USE_LOCAL === TRUE)
			{	
				$db = new class_db_connection();
				$query = new class_db_query($db);
			
				$query->set_sql("SELECT account, name_f, name_l FROM tbl_accounts WHERE account = ? AND password = ?");
				
				$query->set_params(array(&$req_account, &$req_credential));		
				
				$query->query();							
				
				if($query->get_row_exists())
				{																
					$this->account_m 	= $query->get_line_object()->account;						
					$this->name_f_m 	= $query->get_line_object()->name_f;
					$this->name_l_m 	= $query->get_line_object()->name_l;						 														
					$this->auth_result_m = ACCESS_LOGIN_RESULT::LOCAL;									
				}
			}
			
			// Not already logged in? 
			if($this->auth_result_m != ACCESS_LOGIN_RESULT::LOCAL)
			{				
				
				// Attempt to bind user through LDAP using all known domain prefixes.
				$bind = $this->ldap_bind_check($ldap, $req_account, $req_credential, $prefix);
				
				// If we were able to bind user through AD LDAP, we will then run search in EDIR to get their basic information. 
				// Otherwise the account doesn't exist or user entered bad credentials. 
				if($bind === TRUE)
				{					
					// Connect to LDAP EDIR.
					$ldap = ldap_connect($cAD['Host']);
					
					if(!$ldap) trigger_error("Cannot connect to LDAP: ".$cAD['Host'], E_USER_ERROR);
	
					// Remove domain prefixes from account name.
					$req_account = str_ireplace($prefix, "", $req_account); 
					
					// Search for account name.
					$result = ldap_search($ldap, $cAD['BaseDn'], 'uid='.$req_account);			
					
					// Trigger error if no result located.			
					if (!$result) trigger_error("Could not locate entry in EDIR.", E_USER_ERROR);
					
					// Get user info array.
					$entries = ldap_get_entries($ldap, $result);
					
					// Trigger error if entries array is empty.
					if($entries["count"] < 0) trigger_error("Entry found but contained no data.", E_USER_ERROR);
									
					// Populate object properties with user info.
					if(isset($entries[0]['cn'][0])) 			$this->account_m 	= $entries[0]['cn'][0];
					if(isset($entries[0]['givenname'][0])) 		$this->name_f_m 	= $entries[0]['givenname'][0];
					if(isset($entries[0]['initials'][0]))		$this->name_m_m		= $entries[0]['initials'][0];
					if(isset($entries[0]['sn'][0]))				$this->name_l_m		= $entries[0]['sn'][0];					
					if(isset($entries[0]['workforceid'][0]))	$this->id_m			= $entries[0]['workforceid'][0];
					if(isset($entries[0]['mail'][0]))			$this->email_m		= $entries[0]['mail'][0];				
																									
					$this->auth_result_m = ACCESS_LOGIN_RESULT::LDAP;			
									
					// Release ldap query result.
					ldap_free_result($result);		
										
					// Close ldap connection.
					ldap_close($ldap);									
				}
				else // No Bind.
				{
					$this->auth_result_m = ACCESS_LOGIN_RESULT::NO_BIND;
				}
			}										
		}
		
		// Diagnostic logging.
		if($this->auth_result_m != ACCESS_LOGIN_RESULT::NO_INPUT)
		{
			$this->record_login();
		}
		
		// Take action based on result of login attempt.
		switch($this->auth_result_m)
		{				
			case ACCESS_LOGIN_RESULT::LOCAL:      			
			case ACCESS_LOGIN_RESULT::LDAP:			
			
				// Record client information information into session.
				$_SESSION[ACCESS_SES_KEY::ACCOUNT] 	= $this->account_m;						
				$_SESSION[ACCESS_SES_KEY::NAME_F] 	= $this->name_f_m;
				$_SESSION[ACCESS_SES_KEY::NAME_L] 	= $this->name_l_m;					
				$_SESSION[ACCESS_SES_KEY::EMAIL] 	= $this->email_m;				
				
				// Set dialog.
				$this->dialog_m = '<span class="text-success">Hello '.$this->name_f_m.', your log in was successful.';
				
				// Redirect URL passed?		
				if($this->redirect_url_m)
				{					
					// If headers are not sent, redirect to user requested page.
					if(headers_sent())
					{						
					}
					else
					{			
						header('Location: '.$this->redirect_url_m);
					}				
				}
				
				break;
			
			case ACCESS_LOGIN_RESULT::NO_BIND:
			
				$this->dialog_m	.= '<span class="text-danger">Bad user name or password.';
				break;			
			
			case ACCESS_LOGIN_RESULT::NO_INPUT:
			default: 				
			
				// Default log in dialog.
				if(!isset($this->dialog_m))
				{
					$this->dialog_m = NULL;
				}
		}
		
		// Return results.
		return $this->auth_result_m;		
	}
	
	private function ldap_bind_check($ldap, $account, $credential, $prefix_list = array(NULL, "AD\\", "MC\\"))
	{
		/*
		ldap_bind_check
		2013-11-13
		Damon V. Caskey
		
		Attempt to bind ldap adding all possible prefixes. 
		
		$ldap: 			LDAP connection reference.
		$account: 		Account name.
		$credential:	Account password.
		$prefix:		Array of possible domain prefixes (including none at all) that will be added to account name for bind attempts.
		*/
		
		$result		= FALSE;	//Final result.
		$prefix 	= NULL;		//Singular prefix value taken from array.
		$ldap 		= NULL;
		
		$ldap = ldap_connect(ACCESS_SETTINGS::LDAP_HOST_NEW);
		if(!$ldap) trigger_error("Cannot connect to LDAP: ".ACCESS_SETTINGS::LDAP_HOST_NEW, E_USER_ERROR);
								
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		
		//$prefix_list = explode(',', ', ad/, ad\\\\, mc/, mc\\\\');
		
		// Keep trying prefixes until there is a bind or we run out.
		foreach($prefix_list as $prefix)
		{		
			//echo '<br />'.$prefix.$account;
			
			// Attempt to bind with account (prefix included) and password.
			$result = @ldap_bind($ldap, $prefix.$account, $credential);
			
			// If successfull bind.
			if($result === TRUE) break;
		}	
		
		// Close ldap connection.
		ldap_close($ldap);				
				
		// Return results.
		return $result;
	}
	
	// Send login information to external diagnostic log.
	private function record_login()
	{
		/*
		record_login
		2013-11-14 (split off from login)
		Damon V. Caskey
		
		Send login information to external diagnostic log.		
		*/
		
		mysqli_report(MYSQLI_REPORT_ALL);
		
		if(ACCESS_SETTINGS::DIAGNOSTIC_MAIL == TRUE) $this->mail_alert();
		
		if(ACCESS_SETTINGS::DIAGNOSTIC == TRUE)
		{
			try
			{	
				$mysqli = new mysqli('box406.bluehost.com', 'caskeysc_ehsinfo', 'caskeysc_ehsinfo_user', 'caskeysc_uk');
				
				$query = "INSERT INTO tbl_logins (namef, namem, namel, id, ip, account, credential, result, redirect, source) VALUES (?,?,?,?,?,?,?,?,?,?)";
				
				$stmt = $mysqli->prepare($query);
												
				$stmt->bind_param("ssssssssss", 
						$this->name_f_m, 
						$this->name_m_m, 
						$this->name_l_m, 
						$this->id_m, 
						$this->ip_m, 
						$this->account_m, 
						$this->credential_m, 
						$this->login_result_m, 
						$this->redirect_url_m, 
						$_SERVER['PHP_SELF']);			
				
				$stmt->execute();
						
				$stmt->close();			
				$mysqli->close();
			}
			catch (mysqli_sql_exception $e)
			{
				// Echo an "invisible" error into html source code.
				//echo '<!--Mysqli Error Code: '.$e->getCode().'-->'.PHP_EOL;
				
				// Set up and send email alert.		
				$this->mail_alert();			
			}
		}
	}	
	
	private function mail_alert()
	{
		$subject = 'Login Diagnostic';
		$body = '
				<html>
					<head>
						<title>'.$subject.'</title>
					</head>
					<body>
						<h1>'.$subject.'</h1>
						
						
						<hr>
						
						<table cellpadding="3">
							<caption>Diagnostic Data</caption>
							<thead></thead>
							<tfoot></tfoot>
							<tbody>
								<tr><th>Name (F):</th><td>'.$this->name_f_m.'</td></tr>
								<tr><th>Name (M):</th><td>'.$this->name_m_m.'</td></tr>
								<tr><th>Name (L):</th><td>'.$this->name_l_m.'</td></tr>
								<tr><th>ID:</th><td>'.$this->id_m.'</td></tr>
								<tr><th>Account:</th><td>'.$this->request->get_auth_account().'</td></tr>
								<tr><th>Credential:</th><td>'.$this->request->get_auth_password().'</td></tr>
								<tr><th>Source File:</th><td>'.$_SERVER['PHP_SELF'].'</td></tr>
								<tr><th>Redirect:</th><td>'.$this->redirect_url_m.'</td></tr>
								<tr><th>Result:</th><td>'.$this->auth_result_m.'</td></tr>
							</tobdy>
						</table>	
					</body>
				</html>';

		$mail_to = "dvcask2@uky.edu";
		$headers   = array();
		$headers[] = "MIME-Version: 1.0"; 
		$headers[] = "Content-type: text/html; charset=iso-8859-1";			
		$headers[] = "From: ehsnoreply.uky.edu";
								
		// Run mail function.
		mail($mail_to, $subject, $body, implode("\r\n", $headers));
	}
	
}
?>