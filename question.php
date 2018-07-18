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
	$url_query->set_data('fk_id', $obj_navigation_rec->get_fk_id());
	$url_query->set_data('id', $obj_navigation_rec->get_id());
				
	// User access.
	$obj_access = new rocky_class_access();
	$obj_access->access_verify($url_query->return_url_encoded());
	
	// If no module is selected, direct to module listing.
	if($obj_navigation_rec->get_fk_id() == NULL)
	{
		header('Location: module_list.php');
	}
	
	// Initialize our data objects. This is just in case there is no table
	// data for any of the navigation queries to find, we are making new
	// records, or copies of records. It also has the side effect of enabling 
	// IDE type hinting.
	$_main_data = new class_question_data();	
		
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
			$query->set_sql('{call question_delete(@id = ?)}');			
			
			$query->set_params(array(array($_main_data->get_id(), SQLSRV_PARAM_IN)));
			$query->query();
			
			// Refrsh page to the previous record.				
			header('Location: question_list.php?fk_id='.$obj_navigation_rec->get_fk_id());			
				
			break;				
					
		case RECORD_NAV_COMMANDS::SAVE:
			
			// Stop errors in case someone tries a direct command link.
			if($obj_navigation_rec->get_command() != RECORD_NAV_COMMANDS::SAVE) break;
			
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
			$query->set_sql('{call question_update(@id						= ?,
													@fk_id					= ?,
													@title					= ?,
													@notes					= ?,
													@intro					= ?,
													@feedback_correct		= ?,
													@feedback_incorrect		= ?,
													@text					= ?,											
													@log_update				= ?,
													@log_update_by			= ?,
													@log_update_ip			= ?)}');
													
			$params = array($_main_data->get_id(),
						$obj_navigation_rec->get_fk_id(),
						$_main_data->get_title(),
						$_main_data->get_notes(),
						$_main_data->get_intro(),
						$_main_data->get_feedback_correct(),
						$_main_data->get_feedback_incorrect(),
						$_main_data->get_text(),						
						date(DATE_ATOM),
						$obj_access->get_account(),
						$obj_access->get_ip());
			
			$query->set_params($params);			
			$query->query();
			
			// Repopulate main data object with results from merge query.
			$query->get_line_params()->set_class_name('class_question_data');
			$_main_data = $query->get_line_object();
			
			// Sub table: Answers.
			$_obj_data_sub_request = new class_answer_data;
			$_obj_data_sub_request->populate_from_request();			
				
			// If the sub value id is an array, then we know there are 
			// values to update.
			if(is_array($_obj_data_sub_request->get_id()) === TRUE)
			{						
				// Call update stored procedure.
				$query->set_sql('{call answer_update(@fk_id = ?,														 
														@xml = ?,
														@log_update = ?,
														@log_update_by = ?,
														@log_update_ip = ?)}');
														
				$params = array(array($_main_data->get_id(), 			SQLSRV_PARAM_IN),
								array($_obj_data_sub_request->xml(), 	SQLSRV_PARAM_IN),
								array(date(DATE_ATOM), 					SQLSRV_PARAM_IN),
								array($obj_access->get_account(),		SQLSRV_PARAM_IN),
								array($obj_access->get_ip(), 			SQLSRV_PARAM_IN));
				
				$query->set_params($params);			
							
				// Execute the merging query.
				$query->query();
				
				// Now that save operation has completed, reload page using ID from
				// database. This ensures the ID is always up to date, even with a new
				// or copied record.
				header('Location: '.$_SERVER['PHP_SELF'].'?id='.$_main_data->get_id().'&fk_id='.$obj_navigation_rec->get_fk_id());
			}
			
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
	$query->set_sql('{call question_detail(@fk_id = ?,
								@id 			= ?,							
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
					
	$params = array(array($obj_navigation_rec->get_fk_id(), SQLSRV_PARAM_IN),
					array($obj_navigation_rec->get_id(), SQLSRV_PARAM_IN),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array(NULL, 		SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_first,	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_previous, SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_next, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($nav_last, 	SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('class_question_data');
	
	if($query->get_row_exists() === TRUE) $_main_data = $query->get_line_object();
	
	// Sub table (answer) generation
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('class_answer_data');
	
	$_obj_data_sub_list = array();
	if($query->get_row_exists() === TRUE) $_obj_data_sub_list = $query->get_line_object_list();
	
	// Sub table (module) generation.
	// not really a sub table in this case, we just need to
	// get some info about the module for user display
	// and interface purposes.
	$query->get_next_result();
	
	$query->get_line_params()->set_class_name('class_module_data');
	
	if($query->get_row_exists() === TRUE) $_obj_data_sub_module = $query->get_line_object();
		
	// If no question selected, go to the first.
	// If no module is selected, direct to module listing.
	if($obj_navigation_rec->get_id() == NULL)
	{
		$url_query->set_url_base(NULL);
		$url_query->set_data('id', $nav_first);
		
		header('Location: '.$url_query->return_url());
	}
	
	// Populate main navigation with ID's from main data stored procedure.
	$obj_navigation_rec->set_id_first($nav_first);
	$obj_navigation_rec->set_id_previous($nav_previous);
	$obj_navigation_rec->set_id_next($nav_next);
	$obj_navigation_rec->set_id_last($nav_last);

	$obj_navigation_rec->generate_button_list(TRUE, TRUE, TRUE, FALSE, TRUE, FALSE, TRUE, TRUE, TRUE);
	
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?>, Question Detail</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="source/bootstrap/style.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <!-- Place inside the <head> of your HTML -->
		<script type="text/javascript" src="http://ehs.uky.edu/libraries/vendor/tinymce/tinymce.min.js"></script>
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
                <h1>Question Entry - <?php echo $_obj_data_sub_module->get_desc_title(); ?></h1>
                <p>This screen allows for adding and editing module questions.</p>
            </div>
            
            <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">           
           		<?php echo $obj_navigation_rec->get_markup(); ?>  
                
                <p>
                	<a href="question_list.php?fk_id=<?php echo $obj_navigation_rec->get_fk_id(); ?>" class="btn btn-info btn-block" title="Click here to return to the question list screen.">Back to Question List</a>
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
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "material_above_head">Title:</label>
                            
                	<div class="col-sm-10">
                		<input type			= "text" 
                        		class		= "form-control"  
                                name		= "title" 
                                id			= "title" 
                                placeholder	= "Title of content that appears above introduction." 
                                value="<?php echo $_main_data->get_title(); ?>">
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "notes">Notes:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "notes" 
                                    id		= "notes"><?php echo $_main_data->get_notes(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "intro">Introduction:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "intro" 
                                    id		= "intro"><?php echo $_main_data->get_intro(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "feedback_correct">Feedback (Correct):</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "feedback_correct" 
                                    id		= "feedback_correct"><?php echo $_main_data->get_feedback_correct(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "feedback_incorrect">Feedback (Incorrect):</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "feedback_incorrect" 
                                    id		= "feedback_incorrect"><?php echo $_main_data->get_feedback_incorrect(); ?></textarea>
                	</div>
                </div>
                
                <div class="form-group">
                	<label 	class	= "control-label col-sm-2" 
                    		for		= "text">Question Text:</label>
                	
                    <div class="col-sm-10">
                    	<textarea	class	= "form-control wysiwyg" 
                        			rows	= "2" 
                                    name	= "text" 
                                    id		= "text"><?php echo $_main_data->get_text(); ?></textarea>
                	</div>
                </div>
                
                <!---->
                <div class="form-group">                    	
                	<div class="col-sm-offset-2 col-sm-10">
                        <fieldset>
                			<legend>Answers</legend>                    
                            <p>Answers for this question.</p>
                                                                                   
                            <table class="table table-striped table-hover" id="POITable"> 
                                <thead>
                                    <tr>
                                    	<th>Value</th>
                                        <th>Correct</th>
                                        <th colspan="2">Text</th> 
                                    </tr>
                                </thead>
                                <tfoot>
                                </tfoot>
                                <tbody class="answer">                        
                                    <?php                              
                                    if(is_object($_obj_data_sub_list) === TRUE)
									{        
										// Generate table row for each item in list.
										for($_obj_data_sub_list->rewind(); $_obj_data_sub_list->valid(); $_obj_data_sub_list->next())
										{						
											$_obj_data_sub = $_obj_data_sub_list->current();
										
											// Blank IDs will cause a database error, so make sure there is a
											// usable one here.
											if(!$_obj_data_sub->get_id()) $_obj_data_sub->set_id(DB_DEFAULTS::NEW_ID);
										?>
											<tr>
												<td>                                                    	
													<input
                                                    	type    ="text"                                                        	 
														name	="sub_answer_value[]" 
														id		="sub_answer_value_<?php echo $_obj_data_sub->get_id(); ?>" 
														class	="form-control"
                                                        value	="<?php echo $_obj_data_sub->get_value(); ?>" />
												</td>
                                                
                                             	<td>
                                                	<input 
                                						type="radio" 
                                            			name	="sub_answer_correct"
                                                        id		="sub_answer_correct_<?php echo $_obj_data_sub->get_id(); ?>" 
                                            			value	="<?php echo $_obj_data_sub->get_id(); ?>"                                            
                                            			<?php if($_obj_data_sub->get_correct() == TRUE) echo ' checked ';?>>
                                                </td>
                                                
                                                <td>                                               
                                                    <textarea	
                                                    	class	= "form-control" 
                                                        rows	= "2" 
                                                        name	= "sub_answer_text[]" 
                                                        id		= "sub_answer_text_<?php echo $_obj_data_sub->get_id(); ?>"><?php echo $_obj_data_sub->get_text(); ?></textarea>
												</td> 
																							  
												<td>													
													<input 
														type	="hidden" 
														name	="sub_answer_id[]" 
														id		="sub_answer_id_<?php echo $_obj_data_sub->get_id(); ?>" 
														value	="<?php echo $_obj_data_sub->get_id(); ?>" />
														
													<button 
														type	="button" 
														class 	="btn btn-danger btn-sm" 
														name	="row_add" 
														id		="row_del_<?php echo $_obj_data_sub->get_id(); ?>" 
														onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>        
												</td>
											</tr>                                    
									<?php
										}
									}
                                    ?>                        
                                </tbody>                        
                            </table>                            
                            
                            <button 
                                type	="button" 
                                class 	="btn btn-success" 
                                name	="row_add" 
                                id		="row_add_perm"
                                title	="Add new item."
                                onclick	="insRow()">
                                <span class="glyphicon glyphicon-plus"></span></button>
                        </fieldset>
                	</div>                        
                </div>
                <!---->
                                        
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
			$(".answer").append(				
				'<tr>'
					+'<td>'                                                    	
					+	'<input	type="text" name="sub_answer_value[]" id="sub_answer_value_' + $temp_int + '" class	="form-control" value="" />'
					+'</td>'
					
					+'<td>'
						+'<input type="radio" name="sub_answer_correct"	id="sub_answer_correct_' + $temp_int +'" value="">'
					+'</td>'
					
					+'<td>'                                               
						+'<textarea class="form-control" rows="2" name="sub_answer_text[]" id="sub_answer_text_' + $temp_int + '"></textarea>'
					+'</td>'
																  
					+'<td>'													
						+'<input type="hidden" name="sub_answer_id[]" id="sub_answer_id_' + $temp_int + '" value="" />'
							
						+'<button type="button"	class="btn btn-danger btn-sm" name="row_add" id	="row_del_' + $temp_int + '" onclick="deleteRowsub(this)"><span class="glyphicon glyphicon-minus"></span></button>'        
					+'</td>'
				+'</tr>');
					
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