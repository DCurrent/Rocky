<?php 
	
	
	require(__DIR__.'/source/main.php');
	
	//$page_obj = new class_page_cache();

	$access_obj_process = new \dc\stoeckl\process();
	$access_obj_process->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
	$access_obj_process->get_config()->set_use_local(FALSE);
	$access_obj_process->process_control();
	
	//Get and verify log in status.
	$access_obj = new \dc\stoeckl\status();
	$access_obj->get_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);	
	$access_obj->verify();
		
	// Set up navigaiton.
	$navigation_obj = new class_navigation();
	$navigation_obj->generate_markup_nav();
	$navigation_obj->generate_markup_footer();	
	
?>

<!DOCtype html>
<html lang="en">
    <head>
        <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo APPLICATION_SETTINGS::NAME; ?> - Link Blue</title>        
        
         <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
        <link rel="stylesheet" href="source/css/style.css" />
        <link rel="stylesheet" href="source/css/print.css" media="print" />
    </head>
    
    <body>    
        <div id="container" class="container">            
            <?php echo $navigation_obj->get_markup_nav(); ?>                                                                                
            
			
			
            <?php echo $navigation_obj->get_markup_footer(); ?>
        </div><!--container-->        
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-40196994-1', 'uky.edu');
  ga('send', 'pageview');
</script>
		
	<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
		
</body>
</html>

<?php
	// Collect and output page markup.
	//$page_obj->markup_from_cache();	
	//$page_obj->output_markup();
?>