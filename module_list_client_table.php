<?php 
		
	require(__DIR__.'/source/main.php');
	
	//$obj_access = new rocky_class_access();
		
	//$obj_access->access_verify();
	
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
	
	$query->set_sql('{call module_list_client()}');
	$query->query();
	
	$query->get_line_params()->set_class_name('class_module_data');
	$_obj_data_main_list = $query->get_line_object_list();
	
?>

<!DOCtype html>
    <head>
        <title>UK - Environmental Health And Safety</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="source/bootstrap/style.css">
        
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        
        <!-- Latest compiled JavaScript -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        
        <style>			
			
			body {
				background-image: url('/media/image/0_0004.jpg');
				background-position: center center;
				background-repeat: no-repeat;
				background-attachment:fixed;
				background-size: cover;
				background-color: #464646;
			}
			
			.container {
				background-color:#FFF;
				opacity:0.95;
			}
			
			.table tbody>tr>td.vert-align
			{
				vertical-align: middle;
			}
			
		</style>
        
    </head>
    
    <body>
        <div id="container" class="container">
        	<div class="row">
            		<div class="jumbotron">
                    <h1>Environmental Health And Safety</h1>
                    <p>UK Safety begins with YOU!</p>
                    
                    <div id="mainNavigation">
						<?php include($_SERVER['DOCUMENT_ROOT']."/libraries/includes/inc_mainnav_bootstrap.php"); ?>
                    </div>
                    
                  </div>
            
                
            </div>
            
            <div class="row">            
                <div id="subContainer" class="container col-xs-9">                    
                    <div id="content" class="col-xs-12">
                        <table class="table table-striped table-hover">
                            <caption></caption>
                            <thead>
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
												<td><h3 id="<?php echo $_obj_data_main->get_id();?>"><?php echo $_obj_data_main->get_desc_title();?></h3>
													<?php echo $_obj_data_main->get_list_intro(); ?></td>
												<td class="vert-align"><?php
												
														if($_obj_data_main->get_hidden() == 0 || $_obj_data_main->get_hidden() == 1)
														{
														?>
                                                        	<a href="/classes/main.php?cClassID=<?php echo $_obj_data_main->get_id(); ?>" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-play-circle"></span> Go!</a>
                                                        <?php
														};
														?>
                                            	</td>
											</tr>                                    
								<?php								
									}
								}
							?>                                                               
                            	
                            </tbody>
						</table>            
                    </div> 
                </div>
                
                <div id="sidePanel" class="container col-xs-3">		
					<?php //include($_SERVER['DOCUMENT_ROOT']."/a_sidepanel_0001.php"); ?>
                    <img class="img-responsive img-rounded" src="/media/image/building_ehs_front.jpg" alt="EHS Building" style="margin-bottom:20px"> 
                                       
                    <div id="contact_ehs" class="well well-sm">
                        <h4><a href="/">Environmental Health &amp; Safety</a></h4>

<p><a href ="http://ukcc.uky.edu/cgi-bin/dynamo?maps.391+campus+0314" target="_blank">252 East Maxwell Street<br />
    Lexington, KY 40506-0314</a>
</p>

<p>
    Phone: <a href="tel:+18592573845" title="Call 859-257-3845.">859-257-3845</a><br />
    Fax: <a href="tel:+18592578787" title="Call 859-257-8787.">859-257-8787</a>  
</p>  
                    </div><!--/contact_ehs-->
                    
                    <!--Include: C:\inetpub\EHS\a_contact_0001.php--><!--Include: C:\inetpub\EHS\a_accident.php, Last update: 2014-11-16T13:04:20-05:00-->
                    <div id="side_accident_reporting" class="well well-sm">
                        <h4><a href="/ohs/accident.php">Accident Reporting</a></h4>
                        
                        <p>Work Related <span class="color_red">Employee</span> Injuries <br />800-440-6285</p>
                        <p>Student or <span class="color_red">Visitor</span> Injuries<br /> 
                        <a href="/ohs/accident.php">Injury and Illness Reporting</a></p>
                      <p>Report <span class="color_red">unsafe conditions</span> to EHS<br />(859) 257-3827 or <a href="mailto:dwhibb0@email.uky.edu">send us an e-mail</a>.</p>
                        <p> After hours and weekends<br />UKPD <a href="tel:+18592571616" title="Call 859-257-1616.">859-257-1616</a> <span class="color_red">Emergencies</span> <a href="tel:+911" title="Call 911.">911!</a></p> 
                    </div>
                    
                    <div id="SubSideContent" class="well well-sm">
                        <h4><a href="/ehsstaff.php">Environmental Health & Safety Staff</a></h4>
                        
                        <ul> 
                          <li><a href="mailto:dwhibb0@uky.edu" title="Email David Hibbard.">David Hibbard</a><br /> Director<br /><a href="tel:+18592573845" title="Call 859-257-3845.">859-257-3845</a></li>
                    <li><a href="mailto:rdeldr0@uky.edu" title="Email Rachel Eldridge.">Rachel Eldridge</a><br /> Business Officer<br /><a href="tel:+18592571376" title="Call 859-257-1376.">859-257-1376</a></li>
                    <li><a href="mailto:kmcgu1@uky.edu" title="Email Kathryn Childress.">Kathryn Childress</a><br /> Data Coordinator, Sr.<br /><a href="tel:+18592579730" title="Call 859-257-9730.">859-257-9730</a></li>
                    <li><a href="mailto:dvcask2@uky.edu" title="Email Damon Caskey.">Damon Caskey</a><br /> IS Tech Support Specialist III<br /><a href="tel:+18592573241" title="Call 859-257-3241.">859-257-3241</a></li>
                        </ul>
                        
                        <p><a href="/docs/pdf/orgchart.pdf" target="_blank">Organizational Chart</a></p>
                        <p><a href="/ehsstaff.php">Complete EHS Staff Listing</a></p>
                    </div><!-- SubSideContent-->                        
                </div><!-- sidePanel-->                     		
            </div><!-- .row-->      
        </div><!-- container-->

    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-40196994-1', 'uky.edu');
  ga('send', 'pageview');

</script>
</body>
</html>