<?php 
	
	require(__DIR__.'/source/main.php');
	
	// Page caching.
	$page_obj = new class_page_cache();
	ob_start();
			
	// Main navigaiton.
	$obj_navigation_main = new class_navigation();
	$obj_navigation_main->generate_markup_nav();
	$obj_navigation_main->generate_markup_footer();	
				
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(DATABASE::NAME);
	$db_conn_set->set_user('ehsinfo_public');
	$db_conn_set->set_password('eh$inf0');
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);		
			
	// Record navigation.
	$obj_navigation_rec = new class_record_nav();	
	
	// URL request builder.
	$url_query	= new url_query;
	
	$url_query->set_url_base($_SERVER['PHP_SELF']);
	$url_query->set_data('action', $obj_navigation_rec->get_action());
	$url_query->set_data('id', $obj_navigation_rec->get_id());

	// Access control.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
	$access_obj->set_redirect($url_query->return_url());
		
	$access_obj->verify();
	$access_obj->action();
		
	// Initialize our data objects. This is just in case there is no table
	// data for any of the navigation queries to find, we are making new
	// records, or copies of records. It also has the side effect of enabling 
	// IDE type hinting.
	$_main_data = new class_module_data();	
		
	// Ensure the main data ID member is same as navigation object ID.
	$_main_data->set_id($obj_navigation_rec->get_id());
			
	switch($obj_navigation_rec->get_action())
	{		
	
		default:		
		case RECORD_NAV_COMMANDS::NEW_BLANK:
		
			break;
			
		case RECORD_NAV_COMMANDS::NEW_COPY:			
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();			
			break;
			
		case RECORD_NAV_COMMANDS::LISTING:
			
			// Direct to listing.				
			header('Location: module_list.php');
			break;
			
		case RECORD_NAV_COMMANDS::DELETE:						
			
			// Populate the object from post values.			
			$_main_data->populate_from_request();
				
			// Call and execute delete SP.
			$query->set_sql('{call module_delete(@id = ?)}');			
			
			$query->set_params(array(array($_main_data->get_id(), SQLSRV_PARAM_IN)));
			$query->query();
			
			// Refrsh page to the previous record.				
			header('Location: '.$_SERVER['PHP_SELF']);			
				
			break;				
					
		case RECORD_NAV_COMMANDS::SAVE:
			
			// Stop errors in case someone tries a direct command link.
			if($obj_navigation_rec->get_command() != RECORD_NAV_COMMANDS::SAVE) break;
			
			$file_name = NULL;
						
			// Save the record. Saving main record is straight forward. We’ll run the populate method on our 
			// main data object which will gather up post values. Then we can run a query to merge the values into 
			// database table. We’ll then get the id from saved record (since we are using a surrogate key, the ID
			// should remain static unless this is a brand new record). 
			
			// If necessary we will then save any sub records (see each for details).
			
			// Finally, we redirect to the current page using the freshly acquired id. That will ensure we have 
			// always an up to date ID for our forms and navigation system.			
		
			// Populate the object from post values.			
			$_main_data->populate_from_request();
		
			// Call update stored procedure.
			$query->set_sql('{call module_update(@id						= ?,
													@desc_title				= ?,
													@desc_short				= ?,
													@email_list				= ?,
													@list_intro				= ?,
													@intro					= ?,
													@material_above_head	= ?,
													@material_above			= ?,
													@material_below_head	= ?,
													@material_below			= ?,
													@instr_head				= ?,
													@instr					= ?,
													@responsible_party		= ?,
													@cert_verbiage			= ?,
													@field_comments			= ?,
													@field_facility			= ?,
													@field_dept				= ?,
													@field_addroom			= ?,
													@field_mail				= ?,
													@field_email			= ?,
													@field_training_status	= ?,
													@field_etrax			= ?,
													@field_uk_status		= ?,
													@field_ukid				= ?,
													@field_supervisor		= ?,
													@field_phone			= ?,
													@hidden					= ?,	
													@question_order			= ?,	
													@question_quantity		= ?,		
													@log_update				= ?,
													@log_update_by			= ?,
													@log_update_ip			= ?)}');
													
			$params = array($_main_data->get_id(),
						$_main_data->get_desc_title(),
						$_main_data->get_desc_short(),
						$_main_data->get_email_list(),
						$_main_data->get_list_intro(),
						$_main_data->get_intro(),
						$_main_data->get_material_above_head(),
						$_main_data->get_material_above(),
						$_main_data->get_material_below_head(),
						$_main_data->get_material_below(),
						$_main_data->get_instr_head(),
						$_main_data->get_instr(),
						$_main_data->get_responsible_party(),
						$_main_data->get_cert_verbiage(),
						$_main_data->get_field_comments(),
						$_main_data->get_field_facility(),
						$_main_data->get_field_dept(),
						$_main_data->get_field_addroom(),
						$_main_data->get_field_mail(),
						$_main_data->get_field_email(),
						$_main_data->get_field_training_status(),
						$_main_data->get_field_etrax(),
						$_main_data->get_field_uk_status(),
						$_main_data->get_field_ukid(),
						$_main_data->get_field_supervisor(),
						$_main_data->get_field_phone(),
						$_main_data->get_hidden(),
						$_main_data->get_question_order(),
						$_main_data->get_question_quantity(),
						date(DATE_ATOM),
						$obj_access->get_account(),
						$obj_access->get_ip());
			
			$query->set_params($params);			
			$query->query();
			
			// Repopulate main data object with results from merge query.
			$query->get_line_params()->set_class_name('class_module_data');
			$_main_data = $query->get_line_object();
			
			// Refrsh page to reload the record.				
			header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id());
			
			break;			
	}
	
	// Last thing to do before moving on to main html is to get data to populate objects that
	// will then be used to generate forms and subforms. This may have already been done, 
	// such as when making copies of a record, but normally only a only blank object 
	// will exist at this point. We run a basic select query from our current ID and 
	// if a row is found overwrite whatever is in the main data object. If needed, we
	// repeat the process for any sub queries and forms.
	//
	// If there is no row at all found, nothing will be done - this is intended behavior because
	// there could be several reasons why no record is found here and we don't want to have 
	// overly complex or repetitive logic, but that does mean we have to make sure there
	// has been an object established at some point above.
	
	//////
	$query->set_sql('{call module_detail(@id = ?,							
								@sort_field 	= ?,
								@sort_order 	= ?,
								@nav_first		= ?,
								@nav_previous	= ?,
								@nav_next		= ?,
								@nav_last		= ?)}');	
	$nav_first 		= NULL;
	$nav_previous	= NULL;
	$nav_next		= NULL;
	$nav_last 		= NULL;
					
	$params = array(array($obj_navigation_rec->get_id(), SQLSRV_PARAM_IN),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_first,	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_previous, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_next, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_last, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('class_module_data');
	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();
	
	// Sub table (access) generation
	//$query->get_next_result();
	
	//$query->get_line_params()->set_class_name('class_role_access_data');
	
	//$_obj_data_sub_arr = array();
	//if($query->get_row_exists() === TRUE) $_obj_data_sub_arr = $query->get_line_object_list();
	
	
	// Datalist list generation.
	$_obj_data_list_status = NULL;
	
	$query->set_sql('{call instructor_list}');
		
	$query->query();
	$query->get_line_params()->set_class_name('class_instructor_data');
	
	// Populate data object array with results, or a single object if no
	// rows were found.
	$_obj_data_list_instructor_list = $query->get_line_object_list();	
	
		
	// Populate main navigation with ID's from main data stored procedure.
	$obj_navigation_rec->set_id_first($nav_first);
	$obj_navigation_rec->set_id_previous($nav_previous);
	$obj_navigation_rec->set_id_next($nav_next);
	$obj_navigation_rec->set_id_last($nav_last);

	$obj_navigation_rec->generate_button_list();
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Module Detail</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="source/bootstrap/style.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="source/bootstrap/script.js"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="source/tinymce/tinymce.js"></script>
        <script type="text/javascript">
        tinymce.init({
            selector: ".wysiwyg",
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
        </script>
        
        <style>		
						
			.incident {
				font-size:larger;			
			}
			
			ul.checkbox  { 
				
			 	-webkit-column-count: auto;  				
				-moz-column-count: auto;				
			  column-count: auto;			 
			  margin: 0; 
			  padding: 0; 
			  margin-left: 20px; 
			  list-style: none;			  
			} 
			
			ul.checkbox li input { 
			  margin-right: .25em; 
			  cursor:pointer;
			} 
			
			ul.checkbox li { 
			  border: 1px transparent solid; 
			  display:inline-block;
			  width:12em;			  
			} 
			
			
		
		</style>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $obj_navigation_main->get_markup_nav(); ?>                                                                                
            <div class="page-header">           
                <h1>Module Entry</h1>
                <p>This screen allows for adding and editing individual training modules.</p>
            </div>
            
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->get_markup(); ?>  
            
            	<p>
                	<a href="question_list.php?fk_id=<?php echo $obj_navigation_rec->get_id(); ?>" class="btn btn-info btn-block" title="Click here to view, and, or edit questions for this training module.">View / Edit Questions</a>
                </p>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="created">Created:</label>
                	<div class="col-sm-10">
                		<input 
                        	type	="text" 
                            class	="form-control"  
                            name	="created" 
                            id		="created" 
                            placeholder="Time record was created." readonly 
                            value="<?php if(is_object($_main_data->get_log_create())) echo date(DATE_ATOM, $_main_data->get_log_create()->getTimestamp()); ?>">
                	</div>
                </div>
                
             	<div class="form-group">       
                    <label class="control-label col-sm-2" for="last_update">Last Update:</label>
                	<div class="col-sm-10">
                		<input 
                        	type	="text" 
                            class	="form-control"  
                            name	="last_update" 
                            id		="last_update" 
                            placeholder="Time record was updated." readonly 
                            value="<?php if(is_object($_main_data->get_log_update())) echo date(DATE_ATOM, $_main_data->get_log_update()->getTimestamp()); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="desc_title">Title:</label>
                	<div class="col-sm-10">
                		<input type="text" class="form-control"  name="desc_title" id="desc_title" placeholder="Title" value="<?php echo $_main_data->get_desc_title(); ?>" required>
                	</div>
                </div>
                               
                <div class="form-group">
                	<label class="control-label col-sm-2" for="desc_short">Descriptive Title:</label>
                	<div class="col-sm-10">
                		<input type			= "text" 
                        		class		= "form-control"  
                                name		= "desc_short" 
                                id			= "desc_short" 
                                placeholder	= "Descriptive Title" 
                                value="<?php echo $_main_data->get_desc_short(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="">Online Accessibility:</label>
                	<div class="col-sm-10"> 
                    		<div class="radio">
                            	<label><input 
                                			type	= "radio" 
                                            name	= "hidden"
                                            id 		= "hidden_0"
                                            value	= "0"                                   
											<?php if($_main_data->get_hidden() === 0) echo ' checked ';?>>Open (Listed)</label>
                       	 	</div>
                       
                       		<div class="radio">
                            	<label><input 
                                			type	= "radio" 
                                            name	= "hidden"
                                            id 		= "hidden_1"
                                            value	= "1"                                   
											<?php if($_main_data->get_hidden() === 1) echo ' checked ';?>>Open (Unlisted)</label>
                       	 	</div>  
                            
                            <div class="radio">
                            	<label><input 
                                			type	= "radio" 
                                            name	= "hidden"
                                            id 		= "hidden_2" 
                                            value	= "2"                                   
											<?php if($_main_data->get_hidden() === 2) echo ' checked ';?>>Whitelist Only</label>
                       	 	</div>  
                            
                            <div class="radio">
                            	<label><input 
                                			type	= "radio" 
                                            name	= "hidden" 
                                            id 		= "hidden_3"
                                            value	= "3"                                   
											<?php if($_main_data->get_hidden() === 3) echo ' checked ';?>>Closed</label>
                       	 	</div>                  
                                             
                	</div>
				</div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="">Question Order:</label>
                	<div class="col-sm-10">    
                       		<div class="radio">
                            	<label><input 
                                			type	= "radio" 
                                            name	= "question_order"
                                            id 		= "question_order_0"
                                            value	= "0"                                   
											<?php if($_main_data->get_question_order() == 0) echo ' checked ';?>>Random</label>
                       	 	</div>  
                            
                            <div class="radio">
                            	<label><input 
                                			type	= "radio" 
                                            name	= "question_order"
                                            id 		= "question_order_1" 
                                            value	= "1"                                   
											<?php if($_main_data->get_question_order() === 1) echo ' checked ';?>>Linear</label>
                       	 	</div>            
                                             
                	</div>
				</div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "question_quantity">Question Quantity:</label>
                            
                	<div class="col-sm-10">                    
                		<input type			= "number" 
                        		class		= "form-control"  
                                name		= "question_quantity" 
                                id			= "question_quantity"
                                min			= "0"
                                max 		= "50"
                                step 		= "1" 
                                value="<?php echo $_main_data->get_question_quantity(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="responsible_party">Instructor (Responsible party):</label>
                	<div class="col-sm-10"> 
                		<select class		= "form-control"
                                name		= "responsible_party" 
                                id			= "responsible_party">
                        	<?php
								if(is_object($_obj_data_list_instructor_list) === TRUE)
								{
									for($_obj_data_list_instructor_list->rewind(); $_obj_data_list_instructor_list->valid(); $_obj_data_list_instructor_list->next())
									{						
										$_obj_data_list_instructor = $_obj_data_list_instructor_list->current();
										
										$selected = NULL;
										
										if($_obj_data_list_instructor->get_id() === $_main_data->get_responsible_party())
										{
											$selected = ' selected ';
										}
										?>
                                        	<option value="<?php echo $_obj_data_list_instructor->get_id(); ?>" <?php echo $selected; ?>><?php echo $_obj_data_list_instructor->get_name_l().', '.$_obj_data_list_instructor->get_name_f(); ?></option>
                                        <?php										
									}
								}
							?>
                    	</select>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "list_intro">List Intro:</label>
                            
                	<div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "list_intro" 
                                    id		= "list_intro"><?php echo $_main_data->get_list_intro(); ?></textarea>                		
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "material_above_head">Content Above Introduction (Header):</label>
                            
                	<div class="col-sm-10">
                		<input type			= "text" 
                        		class		= "form-control"  
                                name		= "material_above_head" 
                                id			= "material_above_head" 
                                placeholder	= "Title of content that appears above introduction." 
                                value="<?php echo $_main_data->get_material_above_head(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "material_above">Content Above Introduction:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "material_above" 
                                    id		= "material_above"><?php echo $_main_data->get_material_above(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label class="control-label col-sm-2" for="intro">Introduction:</label>
                	<div class="col-sm-10">
                    	<textarea class="form-control wysiwyg" rows="5" name="intro" id="intro"><?php echo $_main_data->get_intro(); ?></textarea>
                	</div>
                </div> 
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "material_below_head">Content Below Introduction (Header):</label>
                            
                	<div class="col-sm-10">
                		<input type			= "text" 
                        		class		= "form-control"  
                                name		= "material_below_head" 
                                id			= "material_below_head" 
                                placeholder	= "Title of content that appears below introduction." 
                                value="<?php echo $_main_data->get_material_below_head(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "material_below">Content below Introduction:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "material_below" 
                                    id		= "material_below"><?php echo $_main_data->get_material_below(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "instr_head">Instructions (Header):</label>
                            
                	<div class="col-sm-10">
                		<input type			= "text" 
                        		class		= "form-control"  
                                name		= "instr_head" 
                                id			= "instr_head" 
                                placeholder	= "Title of instructions." 
                                value="<?php echo $_main_data->get_instr_head(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "instr">Instructions:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "instr" 
                                    id		= "instr"><?php echo $_main_data->get_instr(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "email_list">Email List:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control" 
                        			rows	= "2" 
                                    name	= "email_list" 
                                    id		= "email_list"><?php echo $_main_data->get_email_list(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "cert_verbiage">Certificate Verbiage:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "cert_verbiage" 
                                    id		= "cert_verbiage"><?php echo $_main_data->get_cert_verbiage(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">                    	
                    <div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                            <legend>Registration Fields</legend>
                            
                            <p>Toggle fields that will be presented to and required from the end user for training regestration.</p>
                                        
                            <ul class="checkbox">
                                <li>
                                    <input name="field_comments" id="filed_comments" value="1" type="checkbox" <?php if($_main_data->get_field_comments() == TRUE) echo ' checked '; ?>>
                                    <label for="field_comments">Comments</label>
                                </li> 
                                    
                                <li>
                                    <input name="field_facility" id="field_facility" value="1" type="checkbox" <?php if($_main_data->get_field_facility() == TRUE) echo ' checked '; ?>>
                                    <label for="field_facility">Location</label>
                                </li> 
                                
                                <li>
                                    <input name="field_addroom" id="field_addroom" value="1" type="checkbox" <?php if($_main_data->get_field_addroom() == TRUE) echo ' checked '; ?>>
                                    <label for="field_addroom">Ex. Location</label>
                                </li>
                                    
                                <li>
                                    <input name="field_dept" id="field_dept" value="1" type="checkbox" <?php if($_main_data->get_field_dept() == TRUE) echo ' checked '; ?>>
                                    <label for="field_dept">Department</label>
                                </li> 
                                    
                                <li>
                                    <input name="field_mail" id="field_mail" value="1" type="checkbox" <?php if($_main_data->get_field_mail() == TRUE) echo ' checked '; ?>>
                                    <label for="field_mail">Mail</label>
                                </li>
                                
                                <li>
                                    <input name="field_email" id="field_email" value="1" type="checkbox" <?php if($_main_data->get_field_email() == TRUE) echo ' checked '; ?>>
                                    <label for="field_email">E-Mail</label>
                                </li>
                                
                                <li>
                                    <input name="field_training_status" id="field_training_status" value="1" type="checkbox" <?php if($_main_data->get_field_training_status() == TRUE) echo ' checked '; ?>>
                                    <label for="field_training_status">Training Status</label>
                                </li>
                                
                                <li>
                                    <input name="field_etrax" id="field_etrax" value="1" type="checkbox" <?php if($_main_data->get_field_etrax() == TRUE) echo ' checked '; ?>>
                                    <label for="field_etrax">E-Trax</label>
                                </li>
                                
                                <li>
                                    <input name="field_uk_status" id="field_uk_status" value="1" type="checkbox" <?php if($_main_data->get_field_uk_status() == TRUE) echo ' checked '; ?>>
                                    <label for="field_uk_status">UK Status</label>
                                </li>
                                
                                <li>
                                    <input name="field_ukid" id="field_ukid" value="1" type="checkbox" <?php if($_main_data->get_field_ukid() == TRUE) echo ' checked '; ?>>
                                    <label for="field_ukid">UK ID</label>
                                </li>
                                
                                <li>
                                    <input name="field_supervisor" id="field_supervisor" value="1" type="checkbox" <?php if($_main_data->get_field_supervisor() == TRUE) echo ' checked '; ?>>
                                    <label for="field_supervisor">Supervisor</label>
                                </li>
                                
                                <li>
                                    <input name="field_phone" id="field_phone" value="1" type="checkbox" <?php if($_main_data->get_field_phone() == TRUE) echo ' checked '; ?>>
                                    <label for="field_phone">Phone</label>
                                </li>                                  
                    		</ul>                                    
                    	</fieldset>
                    </div>
                </div>
                                        
                <hr />
                <div class="form-group">
                	<div class="col-sm-12">
                		<?php echo $obj_navigation_rec->get_markup_cmd_save_block(); ?>
                	</div>
                </div>               
            </form>
            
            <?php echo $obj_navigation_main->get_markup_footer(); ?>
        </div><!--container-->        
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-40196994-1', 'uky.edu');
  ga('send', 'pageview');
  
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

	<script>
		 var $temp_int = 0;
		 
		function deleteRowsub(row)
		{
			var i=row.parentNode.parentNode.rowIndex;
			document.getElementById('POITable').deleteRow(i);
		}
		
		// Inserts a new table row on user request.
		function insRow()
		{						
			$(".ec").append(
				'<tr>'
					+'<td>'
						+'<input type="text" name="sub_access_access[]" id="sub_access_access_'+ $temp_int + '" class="form-control" />'												
					+'</td>'					
					+'<td colspan="2">'
						+'<input type="hidden" name="sub_access_id[]" id="sub_access_id_js_'+$temp_int+'" value="<?php echo DB_DEFAULTS::NEW_ID; ?>" />'
						+'<button type="button" class ="btn btn-danger btn-sm" name="row_add" id="row_del_js_'+$temp_int+'" onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>'						
					+'</td>'
				+'<tr>');
			
			tinymce.init({
            selector: '#sub_details_'+ $temp_int,
			plugins: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"});
			
			$temp_int++;
		}		 
	</script>
</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>