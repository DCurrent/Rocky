<?php

	require_once(__DIR__.'/main.php');

	class class_navigation
	{
		private
			$access_obj			= NULL,
			$directory_local	= NULL,
			$directory_prime	= NULL,
			$markup_nav			= NULL,
			$markup_footer		= NULL;
		
		public function __construct()
		{
			$this->directory_prime 	= APPLICATION_SETTINGS::DIRECTORY_PRIME;
			$this->access_obj		= new \dc\stoeckl\status();
			
			$this->access_obj->get_member_config()->set_authenticate_url(APPLICATION_SETTINGS::AUTHENTICATE_URL);
		}
		
		public function get_directory_local()
		{
			return $this->directory_local;
		}
		
		public function get_directory_prime()
		{
			return $this->get_directory_prime();
		}
		
		public function set_directory_local($value)
		{
			$this->directory_local = $value;
		}
		
		public function get_markup_footer()
		{
			return $this->markup_footer;
		}
		
		public function get_markup_nav()
		{
			return $this->markup_nav;
		}
			
		public function generate_markup_nav()
		{
			$class_add = NULL;
			         
            
            //echo '<!-- Account: '.$this->access_obj->get_member_account().'-->';
			if(!$this->access_obj->get_member_account()) $class_add .= "disabled";
			
			// Start output caching.
			ob_start();
		?>
        	
        
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
				<a class="navbar-brand" href="<?php echo $this->directory_prime; ?>"><?php echo APPLICATION_SETTINGS::NAME; ?></a>
				
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Class Modules
							</a>
							<div class="dropdown-menu" aria-labelledby="navbarDropdown">
								<a class="dropdown-item" href="#">Select a Class Module</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="<?php echo $this->directory_prime; ?>/module_list.php">Module List</a>
							</div>
						</li>
					</ul>
					<div class="float-right">

						<?php
							if($this->access_obj->get_member_account())
							{
						?>
								<a href="<?php echo $this->access_obj->get_member_config()->get_authenticate_url(); ?>?auth_logoff=<?php echo TRUE; ?>"><span class="glyphicon glyphicon-log-out"></span> <?php echo $this->access_obj->name_full(); ?></a>
						<?php
							}
							else
							{
						?>
								<a href="<?php echo $this->access_obj->get_member_config()->get_authenticate_url(); ?>"><span class="glyphicon glyphicon-log-in"></span> Guest</a>
						<?php
							}
						?>
					</div>
				</div>				
			</nav>                	
        <?php
			
			// Collect contents from cache and then clean it.
			$this->markup_nav = ob_get_contents();
			ob_end_clean();	
			
			return $this->markup_nav;
		}			
		
		public function generate_markup_footer()
		{
			ob_start();
			?>
			
			<br><br>
			<div class="card bg-light">
				<div class="card-body">
					
					<img class="float-right d-none d-sm-inline img-responsive pull-right .d-sm-none" src="<?php echo $this->directory_prime; ?>/media/php_logo_1.png" alt="Powered by objected oriented PHP." title="Powered by object oriented PHP." />
					
					<img class="float-left d-none d-sm-inline img-responsive pull-right .d-sm-none" style="margin-right: 15px; width: 100px" src="<?php echo $this->directory_prime; ?>/media/uk_logo_1.png" alt="Powered by objected oriented PHP." title="Powered by object oriented PHP." />
					
					<span class="text-muted small"><?php echo APPLICATION_SETTINGS::NAME; ?> Ver <?php echo APPLICATION_SETTINGS::VERSION; ?></span>
					<br>
					<span class="text-muted small">Developed by: <a href="mailto:dvcask2@uky.edu"><span class="glyphicon glyphicon-envelope"></span> Caskey, Damon V.</a></span>
					<br>
					<span class="text-muted small">Copyright &copy; <?php echo date("Y"); ?>, University of Kentucky.</span>
					<br>
				</div>
			</div>
					

			
			<?php
			// Collect contents from cache and then clean it.
			$this->markup_footer = ob_get_contents();
			ob_end_clean();
			
			return $this->markup_footer;
		}
	}

?>