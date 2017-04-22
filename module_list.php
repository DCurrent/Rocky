<?php 
		
	require(__DIR__.'/source/main.php');
	
	$obj_access = new rocky_class_access();
		
	$obj_access->access_verify();
	
	// Start page cache.
	$page_obj = new class_page_cache();
	ob_start();
		
	// Set up navigaiton.
	$navigation_obj = new class_navigation();
	$navigation_obj->generate_markup_nav();
	$navigation_obj->generate_markup_footer();	
	
	// Set up database.
	$db_conn_set = new class_db_connect_params();
	$db_conn_set->set_name(ROCKY_DATABASE::NAME);
	$db_conn_set->set_user('ehsinfo_public');
	$db_conn_set->set_password('eh$inf0');
	
	$db = new class_db_connection($db_conn_set);
	$query = new class_db_query($db);
		
	$paging = new class_paging;	
	
	$query->set_sql('{call module_list(@page_current 		= ?,														 
										@page_rows 			= ?,
										@page_last 			= ?,
										@row_count_total	= ?)}');
											
	$page_last 	= NULL;
	$row_count 	= NULL;		
	
	$params = array(array($paging->get_page_current(), 	SQLSRV_PARAM_IN), 
					array($paging->get_row_max(), 		SQLSRV_PARAM_IN), 
					array($page_last, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT),
					array($row_count, 					SQLSRV_PARAM_OUT, SQLSRV_PHPTYPE_INT));

	$query->set_params($params);
	$query->query();
	
	$query->get_line_params()->set_class_name('class_module_data');
	$_obj_data_main_list = $query->get_line_object_list();

	// Send control data from procedure to paging object.
	$paging->set_page_last($page_last);
	$paging->set_row_count_total($row_count);
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo ROCKY_APPLICATION_SETTINGS::NAME; ?></title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            <div class="page-header">
                <h1>Modules</h1>
                <p>List of available training modules.</p>
            </div>
            
            <a href="module.php?nav_command=<?php echo RECORD_NAV_COMMANDS::NEW_BLANK; ?>" class="btn btn-success btn-block" title="Click here to start entering a new role."><span class="glyphicon glyphicon-plus"></span> New module</a>
          
            <!--div class="table-responsive"-->
                <table class="table table-striped table-hover">
                    <caption></caption>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Intro</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th><!--Action--></th>
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
                                        <tr>
                                            <td><?php echo $_obj_data_main->get_desc_title(); ?></td>
                                            <td><?php echo $_obj_data_main->get_intro(); ?></td>
                                            <td><?php if(is_object($_obj_data_main->get_log_create()) === TRUE) echo date(DATE_ATOM, $_obj_data_main->get_log_create()->getTimestamp()); ?></td>
											<td><?php if(is_object($_obj_data_main->get_log_update()) === TRUE) echo date(DATE_ATOM, $_obj_data_main->get_log_update()->getTimestamp()); ?></td>

                                            <td><a	href		="module.php?id=<?php echo $_obj_data_main->get_id(); ?>" 
                                            class		="btn btn-info"
                                            title		="View details or edit this item."
                                            ><span class="glyphicon glyphicon-eye-open"></span></a></td>
                                        </tr>                                    
                            <?php								
                            	}
							}
                        ?>
                    </tbody>                        
                </table>  
            <?php 
				echo $paging->generate_paging_markup();
				echo $navigation_obj->get_markup_footer(); 
			?>
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
</body>
</html>

<?php
	// Collect and output page markup.
	$page_obj->markup_from_cache();	
	$page_obj->output_markup();
?>