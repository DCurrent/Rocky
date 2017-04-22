<?php		
	//require_once(__DIR__.'/data_account.php');
	//require_once(__DIR__.'/data_area.php');
	//require_once(__DIR__.'/data_autoclave.php');
	require_once(__DIR__.'/data_common.php');
	require_once(__DIR__.'/data_module.php');
	//require_once(__DIR__.'/data_department.php');
	//require_once(__DIR__.'/data_inspection.php');
	//require(__DIR__.'/data_role.php');
	
	class rocky_class_client_data extends class_common_data
	{
		protected
			$account			= NULL,
			$department			= NULL,
			$email				= NULL,
			$last_update_user	= NULL,
			$name_f				= NULL,
			$name_l				= NULL,
			$phone				= NULL,
			$room				= NULL,
			$status				= NULL,
			$supervisor_name_f	= NULL,
			$supervisor_name_l	= NULL;
		
		// Accessors
		public 
			function get_account()
			{
				return $this->account;
			}
			
			function get_department()
			{
				return $this->department;
			}
			
			function get_email()
			{
				return $this->email;
			}
			
			function get_last_update_user()
			{
				return $this->last_update_user;
			}
			
			function get_name_f()
			{
				return $this->name_f;
			}		
					
			function get_name_l()
			{
				return $this->name_l;
			}
			
			function get_phone()
			{
				return $this->phone;
			}
			
			function get_room()
			{
				return $this->room;
			}
			
			function get_status()
			{
				return $this->status;
			}
			
			function get_supervisor_name_f()
			{
				return $this->supervisor_name_f;
			}
			
			function get_supervisor_name_l()
			{
				return $this->supervisor_name_l;
			}
		
		// Mutators
		public 
			function set_account($value)
			{
				$this->account = $value;
			}
			
			function set_department($value)
			{
				$this->department = $value;
			}
			
			function set_email($value)
			{
				$this->email = $value;
			}
			
			function set_last_update_user($value)
			{
				$this->last_update_user = $value;
			}
			
			function set_name_f($value)
			{
				$this->name_f = $value;
			}
			
			function set_name_l($value)
			{
				$this->name_l = $value;
			}
		
			function set_phone($value)
			{
				$this->phone = $value;
			}
			
			function set_room($value)
			{
				$this->room = $value;
			}
			
			function set_status($value)
			{
				$this->status = $value;
			}
			
			function set_supervisor_name_f($value)
			{
				$this->supervisor_name_f = $value;
			}
			
			function set_supervisor_name_l($value)
			{
				$this->supervisor_name_l = $value;
			}
		
				
	}
	
?>
