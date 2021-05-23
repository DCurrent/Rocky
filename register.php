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
		$obj_navigation_rec = new dc\record_navigation\RecordMenu();
	
	// Prepare redirect url with variables.
		$url_query	= new url_query;	
		$url_query->set_data('id', $obj_navigation_rec->get_id());

	// Access control.
		$access_obj = new \dc\stoeckl\status();
		$access_obj->get_member_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
		$access_obj->set_redirect($url_query->return_url());
		
		$access_obj->verify();
		$access_obj->action();
		
	// Set up database.
		$db_conn_set = new class_db_connect_params();
		$db_conn_set->set_name(DATABASE::NAME);
		//$db_conn_set->set_user('ehsinfo_public');
		//$db_conn_set->set_password('eh$inf0');
		
		$db = new class_db_connection($db_conn_set);
		$query = new class_db_query($db);
	
	// Record navigation and button commands.
		//$obj_navigation_rec = new dc\record_navigation\class_record_nav();
		
		// Prepare redirect url with variables.
		$url_query	= new url_query;
		$url_query->set_data('action', $obj_navigation_rec->get_action());
		$url_query->set_data('id', $obj_navigation_rec->get_id());
	
		$obj_navigation_rec->generate_button_list();
	
	// Client data.	
		
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
			$_main_data->set_account($access_obj->get_member_account());
		}
		
		$query->set_sql('{call client_detail_nonav(@account = ?)}');					
		$params = array(array($_main_data->get_account(), SQLSRV_PARAM_IN));
						
		$query->set_params($params);
		$query->query();
		
		$query->get_line_params()->set_class_name('rocky_class_client_data');
		
		if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();
	
	//////////////////////////////////
	
	// Sub lists
	
		// Module selections
			$_obj_data_list_module_list = new class_common_data();
		
			$query->set_sql('{call module_list_for_select()}');
			$query->query();
			
			$query->get_line_params()->set_class_name('class_common_data');
			
			$_obj_data_list_module_list = array();
			if($query->get_row_exists() === TRUE) $_obj_data_list_module_list = $query->get_line_object_list();
	
	// Command action handling.	
	switch($obj_navigation_rec->get_action())
	{		
	
		default:		
		case dc\record_navigation\RECORD_NAV_COMMANDS::NEW_BLANK:		
			break;
			
		case dc\record_navigation\RECORD_NAV_COMMANDS::NEW_COPY:						
			break;
			
		case dc\record_navigation\RECORD_NAV_COMMANDS::LISTING:
			break;
			
		case dc\record_navigation\RECORD_NAV_COMMANDS::DELETE:
			break;				
		case dc\record_navigation\RECORD_NAV_COMMANDS::SAVE:
					
			// If this is set to false, data is not saved.
			$valid = TRUE;			
			
			// Stop errors in case someone tries a direct command link.
			if($obj_navigation_rec->get_command() != dc\record_navigation\RECORD_NAV_COMMANDS::SAVE) break;
									
			// Save the record. Saving main record is straight forward. We’ll run the populate method on our 
			// main data object which will gather up post values. Then we can run a query to merge the values into 
			// database table. We’ll then get the id from saved record (since we are using a surrogate key, the ID
			// should remain static unless this is a brand new record). 
			
			// If necessary we will then save any sub records (see each for details).
			
			// Finally, we redirect to the current page using the freshly acquired id. That will ensure we have 
			// always an up to date ID for our forms and navigation system.			
		
			// Populate the object from post values.			
			$_main_data->populate_from_request();
			
			// Not used for accounts.
			//$_main_data_label = $_main_data->get_label(); 
			
		
			// Call update stored procedure.
			$query->set_sql('{call client_update(@id 					= ?,
													@account			= ?,
													@log_update 		= ?, 
													@log_update_by		= ?, 
													@log_update_ip 		= ?,														 
													@name_f				= ?,
													@name_l				= ?,	
													@department			= ?,
													@status				= ?,
													@room				= ?,
													@supervisor_name_f 	= ?,
													@supervisor_name_l	= ?,
													@email				= ?,
													@phone				= ?,
													@last_update_user	= ?)}');
													
			$params = array(array($_main_data->get_id(), 				SQLSRV_PARAM_IN),
						array($_main_data->get_account(), 				SQLSRV_PARAM_IN),
						array(date(APPLICATION_SETTINGS::TIME_FORMAT),	SQLSRV_PARAM_IN),
						array($_main_data->get_account(), 				SQLSRV_PARAM_IN),
						array($access_obj->get_ip(), 					SQLSRV_PARAM_IN),
						array($_main_data->get_name_f(), 				SQLSRV_PARAM_IN),						
						array($_main_data->get_name_l(),				SQLSRV_PARAM_IN),						
						array($_main_data->get_department(), 			SQLSRV_PARAM_IN),
						array($_main_data->get_status(),				SQLSRV_PARAM_IN),				
						array($_main_data->get_room(), 					SQLSRV_PARAM_IN),
						array($_main_data->get_supervisor_name_f(), 	SQLSRV_PARAM_IN),
						array($_main_data->get_supervisor_name_l(), 	SQLSRV_PARAM_IN),
						array($_main_data->get_email(), 				SQLSRV_PARAM_IN),
						array($_main_data->get_phone(), 				SQLSRV_PARAM_IN),
						array(date(APPLICATION_SETTINGS::TIME_FORMAT),	SQLSRV_PARAM_IN));
			
			//var_dump($params);
			
			// Did all data verify?
			if($valid === TRUE)
			{
				
				$query->set_params($params);			
				$query->query();
				
				// Repopulate main data object with results from merge query.
				$query->get_line_params()->set_class_name('rocky_class_client_data');
				$_main_data = $query->get_line_object();
			
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
					$dialog = '<p class="alert-danger">One or more fields are missing. All fields must be filled out to register for training.</p>';
				}
				else
				{
					// Now that save operation has completed, redirect back to the training program if user had started one.
					if(isset($_SESSION['rocky_ses_key_class_url']) === TRUE)
					{				
						header('Location: '.$_SESSION['rocky_ses_key_class_url']);					
					}
					else
					{
						$dialog = '<p class="alert-success">Thank you for registering. You may now take one of the available training courses.</p>';
					}
				}
			
				
			}
			
			break;			
	}
?>

<!DOCtype html>
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?> - Register</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
		
		<!-- Latest minified Javascript -->
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="source/tinymce/tinymce.min.js"></script>
                
        <script src="../../libraries/javascript/options_update.js"></script>
    </head>
    
    <body>
    
        <div id="container" class="container"> 
        	<?php echo $obj_navigation_main->get_markup_nav(); ?>                                                                                
            <div class="page-header">
            <h1><?php echo APPLICATION_SETTINGS::NAME; ?> - Register</h1> 
            
            <?php echo $dialog; ?>
            
            <p>Please use the form below to ensure all information is up to date. When you are finished, press the save button. All fields must be filled out in order to take training. If you were attempting to take a module already, you will be redirected back to that module upon saving.</p>
            
            <form action="../../classes/main_verify.php" method="post" entype="multipart/form-data" name="class" id="class" onsubmit="return validate_form_inputs('required')" class="class_register">                
                <input type="hidden" name="id" 		value="<?php echo $_main_data->get_id(); ?>" />
                <input type="hidden" name="account" value="<?php echo $_main_data->get_account(); ?>" />
                
                          
                <fieldset id="fs_location">
                    <legend>Location</legend>
                    
                    <p>To fill out your location, first select the building. Then you may select an area, lab, or room. Buildings are listed in alphabetical order by building name. If you know the building's number (speed sort) you may type the first few characters to quickly locate the building. Rooms are listed by floor, then by room number. You may type the first few characters of a room number to seek it quickly as well.</p>
                    
                    <?php
					   
						// Default selection populate.
						
						// Is there an exisiting room selection? if so, we'll need to find the
						// matching building.
						$_obj_building_get = new class_common_data();
						
						if($_main_data->get_room())
						{								
							$query->set_sql('{call area_detail_nonav(@room_barcode = ?)}');					
							$params = array(array($_main_data->get_room(), SQLSRV_PARAM_IN));
											
							$query->set_params($params);
							$query->query();
							
							$query->get_line_params()->set_class_name('class_common_data');
							
							if($query->get_row_exists() === TRUE) $_obj_building_get = $query->get_line_object();
						}
					?>
                    
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="building_code">Building</label>
                        <div class="col-sm-10">
                            <select name="building_code" 
                                id="building_code" 
                                data-current="<?php echo $_obj_building_get->get_id(); ?>" 
                                data-source-url="../../libraries/inserts/facility.php" 
                                data-extra-options='<option value="">Select Facility</option>'
                                data-col_order=""
                                data-grouped="1"
                                class="room_search form-control">
                                    <!--This option is for valid HTML5; it is overwritten on load.--> 
                                    <option value="">Select Facility</option>                                    
                                    <!--Options will be populated on load via jquery.-->                                 
                            </select>
                        </div>
                    </div> 
                                   
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="room">Room, Lab, or Area</label>
                        <div class="col-sm-10">
                            <select name="room" 
                                id="room" 
                                data-current="<?php echo $_main_data->get_room(); ?>" 
                                data-source-url="../../libraries/inserts/room.php" 
                                data-grouped="1" 
                                data-extra-options='<option value="">Select Room/Area/Lab</option>' 
                                class="room_code_search disable form-control"    
                                    
                                    <?php 
										// If the client already has a room barcode, we will
										// need to generate a room list right away instead of 
										// waiting to do it after a building selection. Then we 
										// will also need to mark the current room as selected.
                                        if($_main_data->get_room())
                                        {
                                    ?>
                                        >
                                        <!--Options will be populated/replaced on load via jquery.-->
                                        <option value="">Select Room/Area/Lab</option>
                                    <?php
                                            $_obj_location = new location_class_location();
                                            
                                            $_obj_location->set_room_barcode($_main_data->get_room());
                                            
                                            $_obj_location->create_room_list_from_room();
                                            
                                            echo $_obj_location->get_markup();	
                                        }
                                        else
                                        {
                                        ?>
                                            disabled>
                                            <!--Options will be populated/replaced on load via jquery.-->
                                            <option value="">Select Room/Area/Lab</option>
                                        <?php
                                        }
                                    ?>                                                                            							
                            </select> 
                        </div>                                   
                    </div>
                </fieldset>
                

                <fieldset id="fs_id">
                    <legend>UK Identification</legend>

					<div class="form-group row">
                		<label class="control-label col-sm-2" for="account">Account</label>
						<div class="col-sm-10">
							<input type="text" class="form-control"  name="account" id="account" placeholder="jdoe123" value="<?php echo $_main_data->get_account(); ?>" required>
						</div>
                	</div>
					                   
                    <?php 
						
						$client_name_f = $_main_data->get_name_f();
						
						if($client_name_f == NULL)
						{
							$client_name_f = $access_obj->get_name_f();
						}
					
					?>
                    
					<div class="form-group row">
                		<label class="control-label col-sm-2" for="name_f">First Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control"  name="name_f" id="name_f" placeholder="John" value="<?php echo $client_name_f; ?>" required>
						</div>
                	</div>
					
                    <?php 
						
						$client_name_l = $_main_data->get_name_l();
						
						if($client_name_l == NULL)
						{
							$client_name_l = $access_obj->get_name_l();
						}
					
					?>
                    
					<div class="form-group row">
                		<label class="control-label col-sm-2" for="name_l">Last Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control"  name="name_l" id="name_l" placeholder="Doe" value="<?php echo $client_name_l; ?>" required>
						</div>
                	</div>
					                    
                    <?php
						
						// --Departments
						$_obj_field_source_department_list = new class_common_data();
					
						$query->set_sql('{call department_list()}');
						$query->query();
						
						$query->get_line_params()->set_class_name('class_common_data');
						
						$_obj_field_source_department_list = array();
						if($query->get_row_exists() === TRUE) $_obj_field_source_department_list = $query->get_line_object_list();
                    ?>          
                            
                                
                        <div class="form-group row">
                            <label class="control-label col-sm-2" for="department">Department</label>
                            <div class="col-sm-10">
                                <select name="department" 
                                    id="department"
                                    class="form-control">
                                        <option value="">Select Department</option>
                                        
                                        <?php																
                                            if(is_object($_obj_field_source_department_list) === TRUE)
                                            {        
                                                // Generate table row for each item in list.
                                                for($_obj_field_source_department_list->rewind();	$_obj_field_source_department_list->valid(); $_obj_field_source_department_list->next())
                                                {	                                                               
                                                    $_obj_field_source_department = $_obj_field_source_department_list->current();
                                                                                                                            
                                                    $dept_selected 	= NULL;
                                                    
                                                    // Is there is a current selection at all?
                                                    if($_main_data->get_status() != NULL)
                                                    {
                                                        // Does the current selection value = the ID of our current loop? 
                                                        if($_main_data->get_department() == $_obj_field_source_department->get_id())
                                                        {
                                                            // Set a NULL selection text variable with appropriate selected 
                                                            // markup to insert in the field.
                                                            $dept_selected = ' selected ';
                                                        }								
                                                    }                                          
                                                    
                                                    ?>
                                                    <option value="<?php echo $_obj_field_source_department->get_id(); ?>" <?php echo $dept_selected; ?>><?php echo $_obj_field_source_department->get_id().' - '.$_obj_field_source_department->get_label(); ?></option>
                                                    <?php                                
                                                }
                                            }
                                        ?>                                    
                                                                         
                                </select>
                            </div>
                        </div> 
                                 
                           
                    <?php
                        				
						$_obj_field_source_uk_status_list = new class_common_data();
					
						$query->set_sql('{call uk_status_list()}');
						$query->query();
						
						$query->get_line_params()->set_class_name('class_common_data');
						
						$_obj_field_source_uk_status_list = array();
						if($query->get_row_exists() === TRUE) $_obj_field_source_uk_status_list = $query->get_line_object_list();
						{
                    ?>                        
                           
                            <div class="form-group row">
                                <label class="control-label col-sm-2" for="status">UK Status</label>                                    
                                
                                <div class="col-sm-10">
                                            
                                    <?php																
                                        if(is_object($_obj_field_source_uk_status_list) === TRUE)
                                        {        
                                            // Generate table row for each item in list.
                                            for($_obj_field_source_uk_status_list->rewind();	$_obj_field_source_uk_status_list->valid(); $_obj_field_source_uk_status_list->next())
                                            {	
                                                // Set the single object to the list object in this loop iteration.                                               
                                                $_obj_field_source_uk_status = $_obj_field_source_uk_status_list->current();
                                                
                                                // Now let's add current or default data.
                                                    
                                                    // Set a NULL selection text variable.
                                                    $uk_status_selected = NULL;
                                                    
                                                    // Is there is a current selection at all?
                                                    if($_main_data->get_status() != NULL)
                                                    {
                                                        // Does the current selection value = the ID of our current loop? 
                                                        if($_main_data->get_status() == $_obj_field_source_uk_status->get_id())
                                                        {
                                                            // Set a NULL selection text variable with appropriate selected 
                                                            // markup to insert in the field.
                                                            $uk_status_selected = ' checked ';
                                                        }								
                                                    }
                                                
                                                ?>
                                                    <div class="radio">                                      	
                                                        <label class="radio">
                                                            <input 
                                                                type	="radio" 
                                                                name	="status" 
                                                                id		="status_<?php echo $_obj_field_source_uk_status->get_id(); ?>"
                                                                value	="<?php echo $_obj_field_source_uk_status->get_id(); ?>" <?php echo $uk_status_selected; ?> 
                                                                required>&nbsp;<?php echo $_obj_field_source_uk_status->get_label(); ?>
                                                        </label> 
                                                    </div>
                                                <?php   
                                                                                                                  
                                                                            
                                            }
                                        }
                                    ?>                                                          
                                	<span class="field-validation-valid help-block"></span>   
                                </div>
                            </div>                                       
                           
                    <?php
						}                  
                    ?>   
                    
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="supervisor_name_f">Supervisor First Name</label>
                        <div class="col-sm-10">
                        
                            <input 	type="text"                                                        	 
                                name	="supervisor_name_f" 
                                id		="supervisor_name_f" 
                                class	="form-control"
                                value 	= "<?php echo $_main_data->get_supervisor_name_f(); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="supervisor_name_l">Supervisor Last Name</label>
                        <div class="col-sm-10">
                    
                            <input 	type="text"                                                        	 
                                name	="supervisor_name_l" 
                                id		="supervisor_name_l" 
                                class	="form-control"
                                value 	= "<?php echo $_main_data->get_supervisor_name_l(); ?>">
                        </div>
                    </div>
                	
                                
            	</fieldset>
                
                <fieldset id="fs_contact">
                	<legend>Contact</legend>
                    
                    
                    <?php
					
						// Because of the grandfathered tables and dual registration,
						// populating an existing email is going to be a bit convoluted.
						
						
						// This will be our population variable.
						$current_email = NULL;
						
						// If there is anything in the email field itself, we can just
						// use that.
						if($_main_data->get_email())
						{
							$current_email = $_main_data->get_email();	
						}
						else
						{
							// If the account contains an '@', then this is a custom EHS account
							// that uses email as the account name, so we can just use it as
							// is in the email field. Otherwise, this must be a Active Directory
							// account (yay!). That's best case, but it means we need to
							// add the '@uky.edu' suffix.
							
							$current_email = $_main_data->get_account();
						
							if($current_email == NULL)
							{
								$current_email = $access_obj->get_member_account();
							}							
							
							if(strpos($current_email, '@') !== FALSE)
							{										
							}
							else
							{
								$current_email .= '@uky.edu';
							}
						}
					
					?>
                    					
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="email">Email</label>
                        <div class="col-sm-10">
                    
                            <input 	type="text"                                                        	 
                                name	="email" 
                                id		="email" 
                                class	="form-control"
                                value 	= "<?php echo $current_email; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="control-label col-sm-2" for="phone">Phone</label>
                        <div class="col-sm-10">
                    
                            <input 	type="text"                                                        	 
                                name	="phone" 
                                id		="phone" 
                                class	="form-control"
                                value 	= "<?php echo $_main_data->get_phone(); ?>">
                        </div>
                    </div>                           
                </fieldset>
                
                <hr />                
                <?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
               
            </form>
        </div><!--container-->        
		
		<script>            
            $('.room_search').change(function(event){	
                options_update(event, null, '#room');	
            });
            
            $(document).ready(function(event){
                $('[data-toggle="tooltip"]').tooltip();
            
                options_update(event, null, '#building_code');
				
            });
        </script>
    </body>
</html>

<?php
	// Collect and output page markup.
	//$page_obj->markup_from_cache();	
	//$page_obj->output_markup();
?>

							

