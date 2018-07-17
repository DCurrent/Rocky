<?php

	require_once($_SERVER['DOCUMENT_ROOT'].'/apps/rocky/source/main.php');

	$class_id 	= 31;
	$class_url	= $_SERVER['PHP_SELF'];

	// Prepare redirect url with variables.
		$url_query	= new url_query;	
		$url_query->set_data('id', $class_id);

	// Set up database.
		$db_conn_set = new class_db_connect_params();
		$db_conn_set->set_name(DATABASE::NAME);
		//$db_conn_set->set_user('ehsinfo_public');
		//$db_conn_set->set_password('eh$inf0');
		
		$db = new class_db_connection($db_conn_set);
		$query = new class_db_query($db);

	// User access.
		$access_obj = new rocky_class_access();
		$access_obj->access_verify($url_query->return_url());
	
		$_SESSION['rocky_ses_key_class_id'] 	= $class_id;
		$_SESSION['rocky_ses_key_class_url'] 	= $class_url;
		
		
	
	// User records.
		// Initialize our data objects. This is just in case there is no table
		// data for any of the navigation queries to find, we are making new
		// records, or copies of records. It also has the side effect of enabling 
		// IDE type hinting.
		$_main_data = new rocky_class_client_data();			
		
		// If just opening up, we can't use ID because there is no
		// way to get one. So we'll start with account. Save operations
		// still use IDs, since by then we'll have them or know
		// a new record will be created.
		// Populate the object from request post values.			
		$_main_data->populate_from_request();
		$_client_account = $_main_data->get_account();
	
		// If no account passed through GET, then get account currently logged in.
		if($_client_account == NULL)
		{
			$_main_data->set_account($access_obj->get_account());
		}
		
		$query->set_sql('{call client_detail_nonav(@account = ?)}');					
		$params = array(array($_main_data->get_account(), SQLSRV_PARAM_IN));
						
		$query->set_params($params);
		$query->query();
		
		$query->get_line_params()->set_class_name('rocky_class_client_data');
		
		if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();

?>

<!DOCTYPE HTML>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>ASBESTOS AWARENESS TRAINING - FINAL 121715</title>
    <script src="standard.js" type="text/javascript"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="captivate.css" />
    <style>
		.element_centered {
		  position: fixed;		 
		  left: 50%;		 
		}
	</style>
</head>
	

	

<body   bgcolor="#f2f2f2">

	<?php
	
		if($_main_data->get_name_f() 				== NULL
				|| $_main_data->get_name_l() 			== NULL
				|| $_main_data->get_room() 				== NULL
				|| $_main_data->get_department() 		== NULL
				|| $_main_data->get_status() 			== NULL
				|| $_main_data->get_supervisor_name_f()	== NULL
				|| $_main_data->get_supervisor_name_l()	== NULL
				|| $_main_data->get_email()				== NULL
				|| $_main_data->get_phone()				== NULL)
			{
	?>
				<p class="alert-danger">It appears you need to register for training. <a href="../../register.php">Click here</a> to begin registration.</p>
	
    <?php
			}
			else
			{
			?>
						
				<?php
                $_SESSION['rocky_ses_key_client_id'] 	= $_main_data->get_id();
						
	?>
    <div id="Instructions" style="text-align:center;">
    	<div>
            <h1>Instructions</h1>
            <p>This course is different than some on-line training courses in that instead of a having to take a quiz at the end of the training in which you have to answer a certain percentage correctly to get credit for taking it, you will instead be provided with “Knowledge Verification” questions that will appear after each main subject area. If you answer any of these questions incorrectly you will be automatically taken back to the slide that will have the information necessary to answer the question correctly.  A correct answer will be required before the training will advance.   Therefore, following the completion of the training you will have supplied a correct answer to each question and you will be provided credit for having successfully completing the course. </p>
            <p>On average, the course should take slightly more than 40 minutes but well less than 1 hour to complete.  Also, please note that the course will time-out if you take longer than 1 hour and you will have to start the course over from the beginning.</p>
        
        	<p>If you should have any difficulty or require additional explanation of any of the subject matter provided in the training please contact <a href="mailto:twtayl0@uky.edu">Tommy Taylor</a> (859) 257-5295.</p>
		</div>
	</div>
<div id="CaptivateContent">&nbsp;
	</div>
	<script type="text/javascript">
		var strURLFull = window.document.location.toString();
		var intTemp = strURLFull.indexOf("?");
		var	strURLParams = "";
		if(intTemp != -1)
		{
			strURLParams = strURLFull.substring(intTemp + 1, strURLFull.length);
		}
	    var so = new SWFObject("asbestos.swf", "Captivate", "961", "751", "10.0", "#CCCCCC");
		so.addParam("quality", "high");
		so.addParam("name", "Captivate");
		so.addParam("id", "Captivate");
		so.addParam("wmode", "window");
		so.addParam("bgcolor","#f2f2f2");
		so.addParam("seamlesstabbing","false");
		so.addParam("menu", "false");
		so.addParam("AllowScriptAccess","always");
		so.addVariable("variable1", "value1");
		if(strURLParams != "")
		{
			so.addVariable("flashvars",strURLParams);
		}
		so.setAttribute("redirectUrl", "http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash");
		so.write("CaptivateContent");
	</script>
      

	<script type="text/javascript">
		document.getElementById('Captivate').focus();
		document.Captivate.focus();
	</script>
    
    <?php
		
		}
	?>
    
    <script>
		function postData()
		{			
			$.post("http://ehs.uky.edu/apps/rocky/record.php");			
		}
	</script>
</body>
</html>
