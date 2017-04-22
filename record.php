<?php 
	
	//require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/config.php'); //Blasic configuration file. 
	require_once($_SERVER['DOCUMENT_ROOT'].'/apps/rocky/source/main.php');
	
	/*
	Damon V. Caskey
	2011/11/01
	~2011/12/08
	~2013/01/14
	
	Create training quiz from database entries as identified by class ID.
	*/	
	
		// For messages to user.
		$dialog = NULL;		
	
	// Set up page navigaiton.
		$obj_navigation_main = new class_navigation();
		$obj_navigation_main->generate_markup_nav();
		$obj_navigation_main->generate_markup_footer();
	
	// Record navigation.
		$obj_navigation_rec = new class_record_nav();
	
	// Prepare redirect url with variables.
		$url_query	= new url_query;	
		$url_query->set_data('id', $obj_navigation_rec->get_id());
	
	// User access.
		$access_obj = new rocky_class_access();
		$access_obj->access_verify($url_query->return_url());	
		
	// Set up database.
		$db_conn_set = new class_db_connect_params();
		$db_conn_set->set_name(ROCKY_DATABASE::NAME);
		//$db_conn_set->set_user('ehsinfo_public');
		//$db_conn_set->set_password('eh$inf0');
		
		$db = new class_db_connection($db_conn_set);
		$query = new class_db_query($db);
	
	// Command action handling.	
	
			
			// Not used for accounts.
			//$_main_data_label = $_main_data->get_label(); 
			
		
			// Call update stored procedure.
			$query->set_sql('{call training_update(@client_id			= ?,
													@class_type_id		= ?,
													@trainer_id			= ?,
													@class_date			= ?)}');
			
			$client_id 	= $_SESSION['rocky_ses_key_client_id'];
			$class_id 	= $_SESSION['rocky_ses_key_class_id'];
			$trainer_id = 0;
			
											
			$params = array(array($client_id, 				SQLSRV_PARAM_IN),
						array($class_id, 					SQLSRV_PARAM_IN),
						array($trainer_id,					SQLSRV_PARAM_IN),
						array(date(ROCKY_APPLICATION_SETTINGS::TIME_FORMAT),	SQLSRV_PARAM_IN));
			
			//var_dump($params);
			
			
			$query->set_params($params);			
			$query->query();

?>

<!DOCtype html>
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo ROCKY_APPLICATION_SETTINGS::NAME; ?> - Register</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
                
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>        
        <script src="../../libraries/javascript/options_update.js"></script>
    </head>
    
    <body>
    
        <div id="container" class="container"> 
        	<?php echo $obj_navigation_main->get_markup_nav(); ?>                                                                                
            <div class="page-header">
            <h1><?php echo ROCKY_APPLICATION_SETTINGS::NAME; ?> - Record</h1>  
            
            The training records have been recorded.           
    </body>
</html>

<?php
	// Collect and output page markup.
	//$page_obj->markup_from_cache();	
	//$page_obj->output_markup();
?>

							

