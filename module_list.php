<?php 
		
	require(__DIR__.'/source/main.php');

	// Trim string that exceeds length and append "...".
	function limitStrlen($input, $length, $ellipses = true, $strip_html = true) 
	{		
		//strip tags, if desired
		if ($strip_html) {
			$input = strip_tags($input);
		}

		//no need to trim, already shorter than trim length
		if (strlen($input) <= $length) {
			return $input;
		}

		//find last space within length
		$last_space = strrpos(substr($input, 0, $length), ' ');
		if($last_space !== false) {
			$trimmed_text = substr($input, 0, $last_space);
		} else {
			$trimmed_text = substr($input, 0, $length);
		}
		//add ellipses (...)
		if ($ellipses) {
			$trimmed_text .= '...';
		}

		return $trimmed_text;
	}


	// Access control.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
		
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
		
	$paging_config = new dc\record_navigation\PagingConfig;
	$paging_config->set_url_query_instance(new dc\url_query\URLQuery);
	$paging = new dc\record_navigation\Paging($paging_config);
	
   
    /* 
    * Source query.  Call the stored procedure
    * and send it any filter or sorting params
    * we need.
    */

    $sql_string = 'EXEC module_list :page_current,														 
										:page_rows';

    
    try
    {   
        $dbh_pdo_statement = $dc_yukon_connection->get_member_connection()->prepare($sql_string);
		
	    $dbh_pdo_statement->bindValue(':page_current', $paging->get_page_current(), \PDO::PARAM_INT);
        $dbh_pdo_statement->bindValue(':page_rows', $paging->get_row_max(), \PDO::PARAM_INT);
        
        $dbh_pdo_statement->execute();   
    }
    catch(\PDOException $e)
    {
        die('Database error : '.$e->getMessage());
    }

    /*
    * Build a list of data objects. Each object in the
    * list represents a row of data from our query.
    */

    $_row_object = NULL;
    $_obj_data_main_list = new \SplDoublyLinkedList();

    while($_row_object = $dbh_pdo_statement->fetchObject('class_module_data', array()))
    {       
        $_obj_data_main_list->push($_row_object);
    }
    
    /*
    * Now we need the paging information for 
    * our paging control.
    */

    try
    {         
        $dbh_pdo_statement->nextRowset();        
        
        $_paging_data = $dbh_pdo_statement->fetchObject('dc\record_navigation\data_paging', array());
        
        $paging->set_page_last($_paging_data->get_page_count());
        $paging->set_row_count_total($_paging_data->get_record_count());
    }
    catch(\PDOException $e)
    {
        die('Database error : '.$e->getMessage());
    }

	


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
                <h1>Modules</h1>
                <p>List of available training modules.</p>
            </div>           
          
			<?php
			// Add record button.
			if($target_exists)
			{
			?>
			
				<a href="<?php echo $target_url; ?>&#63;nav_command=<?php echo dc\record_navigation\RECORD_NAV_COMMANDS::NEW_BLANK;?>&amp;id=<?php echo DB_DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block font-weight-bold" title="Click here to start entering a new item.">&#43; New Module</a>
			
			<?php
			}
			?>
			
			<br>
			
			<?php
				echo $paging->generate_paging_markup();
			?>		
            
			<br>
			<br>
			
			<table class="table table-striped table-hover">
				<caption></caption>
				<thead>
					<tr>
						<th>Title</th>
						<!-- th>Notes</th -->
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
								
								
								// Let's keep the the intro within reason, and strip tags to
								// avoid any leftover open tags.
								// As of 2020-11-30, length is limited by Stored Procedure output.
								
								//$intro = strip_tags (limitStrlen($_obj_data_main->get_intro(), 47, true, false),'');
								//$intro = strip_tags ($_obj_data_main->get_intro(),'');
								
						?>
									<tr class="clickable-row" role="button" data-href="<?php echo $_obj_data_main->get_id(); ?>">
										<td><?php echo $_obj_data_main->get_desc_title(); ?></td>
										<!-- <td><?php //echo $intro; ?></td -->
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
			
				<a href="<?php echo $target_url; ?>&#63;nav_command=<?php echo dc\record_navigation\RECORD_NAV_COMMANDS::NEW_BLANK;?>&amp;id=<?php echo DB_DEFAULTS::NEW_ID; ?>" class="btn btn-success btn-block font-weight-bold" title="Click here to start entering a new item.">&#43; New Module</a>
				
				<br>
			<?php
			}
			
				echo $paging->get_markup();
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
						window.document.location = '<?php echo $target_url; ?>?id=' + $(this).data("href");
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