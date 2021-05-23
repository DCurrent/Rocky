<?php 
		
	require(__DIR__.'/source/main.php');
	
	// Access control.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_member_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
		
	$access_obj->verify();
	$access_obj->action();
	
	// Start page cache.
	$page_obj = new class_page_cache();
	ob_start();
		
	// Set up navigaiton.
	$navigation_obj = new class_navigation();
	$navigation_obj->generate_markup_nav();
	$navigation_obj->generate_markup_footer();	
	
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(DATABASE::NAME);
	$db_conn_set->set_user('ehsinfo_public');
	$db_conn_set->set_password('eh$inf0');
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);
	
	// Record navigation.
	$obj_navigation_rec = new dc\record_navigation\RecordMenu();	
	
	// URL request builder.
	$url_query	= new url_query;
	
	$url_query->set_url_base($_SERVER['PHP_SELF']);
	$url_query->set_data('fk_id', $obj_navigation_rec->get_fk_id());

	$paging_config = new dc\record_navigation\PagingConfig;
	$paging_config->set_url_query_instance(new dc\url_query\URLQuery);
	$paging = new dc\record_navigation\Paging($paging_config);
	
	$query->set_sql('{call question_list(@fk_id				= ?,
										@page_current 		= ?,														 
										@page_rows 			= ?,
										@page_last 			= ?,
										@row_count_total	= ?)}');
											
	$page_last 	= NULL;
	$row_count 	= NULL;		
	
	$params = array(array($obj_navigation_rec->get_fk_id(), SQLSRV_PARAM_IN),
					array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN), 
					array($page_last, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($row_count, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('class_question_data');
	$_obj_data_main_list = $query->get_line_object_list();

	// Send control data from procedure to paging object.

	$paging->set_page_last($page_last);
	$paging->set_row_count_total($row_count);

	// Clickable rows. Clicking on table rows
	// should take user to a detail page for the
	// record in that row. To do this we first get
	// the base name of this file, and remove "list".
	// 
	// The detail file will always have same name 
	// without "list". Example: area.php, area_list.php
	//
	// Once we have the base name, we can use script to
	// make table rows clickable by class selector
	// and passing a completed URL (see the <tr> in
	// data table we are making clickable).
	//
	// Just to ease in development, we verify the detail
	// file exists before we actually include the script
	// and build a complete URL string. That way if the
	// detail file is not yet built, clicking on a table
	// row does nothing at all instead of giving the end
	// user an ugly 404 error.
	//
	// Lastly, if the base name exists we also build a 
	// "new item" button that takes user directly
	// to detail page with a blank record.	

	$target_url 	= '#';
	$target_name	= basename(__FILE__, '_list.php').'.php';
	$target_file	= __DIR__.'/'.$target_name;
	$target_exists 	= file_exists($target_file);

	// If the target file is present, then it becomes
	// our target URL. 
	if($target_exists)
	{
		$target_url = $target_name;
	}
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?></title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            <div class="page-header">
                <h1>Questions</h1>
                <p>List of available training questions.</p>
            </div>
            
            <a href="module.php?id=<?php echo $obj_navigation_rec->get_fk_id(); ?>" class="btn btn-info btn-block" title="Click here to return to the module screen.">Back to Module</a><br>           
          		
            <!--div class="table-responsive"-->
                <table class="table table-striped table-hover">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th>Text</th>
                            <th>Created</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
                    <tbody>                        
                        <?php
                            if(is_object($_obj_data_main_list) === TRUE)
							{
								for($_obj_data_main_list->rewind(); $_obj_data_main_list->valid(); $_obj_data_main_list->next())
								{						
									$_obj_data_main = $_obj_data_main_list->current();
                            ?>
                                        <tr class="clickable-row" role="button" data-href="<?php echo $_obj_data_main->get_id(); ?>">
                                            <td><?php echo $_obj_data_main->get_text(); ?></td>
                                            <td><?php if(is_object($_obj_data_main->get_log_create()) === TRUE) echo date(DATE_ATOM, $_obj_data_main->get_log_create()->getTimestamp()); ?></td>
											<td><?php if(is_object($_obj_data_main->get_log_update()) === TRUE) echo date(DATE_ATOM, $_obj_data_main->get_log_update()->getTimestamp()); ?></td>                                            
                                        </tr>                                    
                            <?php								
                            	}
							}
                        ?>
                    </tbody>                        
                </table>
			<?php 
			
			// Add record button.
			if($target_exists)
			{
			?>
			
				<a href="<?php echo $target_url; ?>&#63;nav_command=<?php echo dc\record_navigation\RECORD_NAV_COMMANDS::NEW_BLANK;?>&amp;fk_id=<?php echo $obj_navigation_rec->get_fk_id(); ?>&amp;id=<?php echo dc\yukon\DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block font-weight-bold" title="Click here to start entering a new item.">&#43; New Question</a>
				
				<br>
			<?php
			}
             
				echo $paging->generate_paging_markup();
				echo $navigation_obj->get_markup_footer(); 
			?>
        </div><!--container-->    
		
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
	
	<?php
		
		// Does the file exisit? If so we can
		// use the URL, script, and new 
		// item button.
		if($target_exists)
		{
		?>
			<script>
				// Clickable table row.
				jQuery(document).ready(function($) {
					$(".clickable-row").click(function() {
						window.document.location = '<?php echo $target_url; ?>?fk_id=<?php echo $obj_navigation_rec->get_fk_id(); ?>&id=' + $(this).data("href");
					});
				});
			</script>
		<?php
		}

	?>
</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>