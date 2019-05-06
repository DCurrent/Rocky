<?php		
	
	require_once(__DIR__.'/data_common.php');
	
	class class_instructor_data extends class_common_data
	{
		private
			$department_name,
			$name,
			$name_f,
			$name_l;
			
		public function get_department_name()
		{
			return $this->department_name;
		}
		
		public function get_name()
		{
			return $this->name;
		}
		
		public function get_name_f()
		{
			return $this->name_f;
		}
		
		public function get_name_l()
		{
			return $this->name_l;
		}
	}
	
	class class_answer_data extends class_common_data
	{
		private
			$correct	= NULL,
			$text		= NULL,
			$value		= NULL;
		
		// Get and return an xml string for database use.
		public function xml()
		{
			$result = '<root>';
						
			foreach($this->id as $key => $id)
			{	
				// Correct is a radio button, which returns nothing if not checked. 
				// We'll cover for that here to avoid non existing key warnings.
				$correct = FALSE;
				
				echo '$this->correct '.$this->correct;
				
				if($this->correct == $id)
				{
					$correct = TRUE;
				}
										
				$result .= '<row id="'.$id.'">';
				$result .= '<value>'.$this->value[$key].'</value>';
				$result .= '<correct>'.$correct.'</correct>';
				$result .= '<text>'.htmlspecialchars($this->text[$key]).'</text>';
				$result .= '</row>';									
			}
			
			$result .= '</root>';
			
			return $result;
		}
			
		public function get_correct()
		{
			return $this->correct;
		}
		
		public function get_text()
		{
			return $this->text;
		}
		
		public function get_value()
		{
			return $this->value;
		}
		
		public function set_id($value)
		{
		}
		
		public function set_value($value)
		{
		}
		
		public function set_text($value)
		{
		}
		
		public function set_sub_answer_id($value)
		{
			$this->id = $value;
		}
		
		public function set_sub_answer_correct($value)
		{
			$this->correct = $value;
		}
		
		public function set_sub_answer_value($value)
		{
			$this->value = $value;
		}
		
		public function set_sub_answer_text($value)
		{
			$this->text = $value;
		}
	}
	
	class class_question_data extends class_common_data
	{
		private
			$fk_id				= NULL,
			$display_order		= NULL,
			$title				= NULL,
			$intro				= NULL,
			$notes				= NULL,
			$feedback_correct	= NULL,
			$feedback_incorrect	= NULL,
			$text				= NULL;
		
		public function get_fk_id()
		{
			return $this->fk_id;
		}
			
		public 	function get_display_order()
		{
			return $this->display_order;
		}
		
		public function get_title()
		{
			return $this->title;
		}
		
		public function get_intro()
		{
			return $this->intro;
		}
		
		public function get_notes()
		{
			return $this->notes;
		}
		
		public function get_feedback_correct()
		{
			return $this->feedback_correct;
		}
		
		public function get_feedback_incorrect()
		{
			return $this->feedback_incorrect;
		}
		
		public function get_text()
		{
			return $this->text;
		}
		
		// Mutators
		public function set_fk_id($value)
		{
			$this->fk_id = $value;
		}
		
		public function set_display_order($value)
		{
			$this->display_order = $value;
		}
		
		public function set_title($value)
		{
			$this->title = $value;
		}
		
		public function set_intro($value)
		{
			$this->intro = $value;
		}
		
		public function set_notes($value)
		{
			$this->notes = $value;
		}
		
		public function set_feedback_incorrect($value)
		{
			$this->feedback_incorrect = $value;
		}
		
		public function set_feedback_correct($value)
		{
			$this->feedback_correct = $value;
		}
		
		public function set_text($value)
		{
			$this->text = $value;
		}
	}
	
	// List of fields from training database parameters table.
	class class_module_data extends class_common_data
	{		
		private					 
			$cert_verbiage			= NULL,
			$desc_short				= NULL,
			$desc_title 			= NULL,
			$email_list				= NULL,						
			$field_addroom			= NULL,
			$field_comments			= NULL,
			$field_dept				= NULL,
			$field_email			= NULL,
			$field_etrax			= NULL,
			$field_facility			= NULL,
			$field_mail				= NULL,
			$field_training_status	= NULL,			
			$field_uk_status		= NULL,
			$field_ukid				= NULL,
			$field_phone			= NULL,
			$field_supervisor		= NULL,
			$hidden					= NULL,			
			$intro					= NULL,
			$instr_head				= NULL,
			$instr					= NULL,
			$list_intro				= NULL,
			$material_above			= NULL,
			$material_above_head 	= NULL,			
			$material_below			= NULL,
			$material_below_head	= NULL,
			$question_layout		= NULL,
			$question_order			= NULL,
			$question_quantity		= NULL,
			$responsible_party		= NULL;

		// Accessors
		public function get_new_record()
		{
			return $this->new_record;
		}
		
		public function get_cert_verbiage()
		{
			return $this->cert_verbiage;
		}
		
		public function get_desc_short()
		{
			return $this->desc_short;
		}
		
		public function get_desc_title()
		{
			return $this->desc_title;
		}
		
		public function get_email_list()
		{
			return $this->email_list;
		}
		
		public function get_field_addroom()
		{
			return $this->field_addroom;
		}
		
		public function get_field_comments()
		{
			return $this->field_comments;
		}
		
		public function get_field_dept()
		{
			return $this->field_dept;
		}
		
		public function get_field_email()
		{
			return $this->field_email;
		}
		
		public function get_field_etrax()
		{
			return $this->field_etrax;
		}
		
		public function get_field_facility()
		{
			return $this->field_facility;
		}
		
		public function get_field_mail()
		{
			return $this->field_mail;
		}
		
		public function get_field_training_status()
		{
			return $this->field_training_status;
		}
		
		public function get_field_uk_status()
		{
			return $this->field_uk_status;
		}
		
		public function get_field_ukid()
		{
			return $this->field_ukid;
		}
		
		public function get_field_phone()
		{
			return $this->field_phone;
		}
		
		public function get_field_supervisor()
		{
			return $this->field_supervisor;
		}
		
		public function get_hidden()
		{
			return $this->hidden;
		}
		
		public function get_intro()
		{
			return $this->intro;
		}
		
		public function get_instr_head()
		{
			return $this->instr_head;
		}
		
		public function get_instr()
		{
			return $this->instr;
		}
		
		public function get_list_intro()
		{
			return $this->list_intro;
		}
		
		public function get_material_above()
		{
			return $this->material_above;
		}
		
		public function get_material_above_head()
		{
			return $this->material_above_head;
		}
		
		public function get_material_below()
		{
			return $this->material_below;
		}
		
		public function get_material_below_head()
		{
			return $this->material_below_head;
		}
		
		public function get_question_layout()
		{
			return $this->question_layout;
		}
		
		public function get_question_order()
		{
			return $this->question_order;
		}
		
		public function get_question_quantity()
		{
			return $this->question_quantity;
		}
		
		public function get_responsible_party()
		{
			return $this->responsible_party;
		}	
					
		// Mutators
		public function set_new_record($value)
		{
			$this->new_record = $value;
		}
		
		public function set_cert_verbiage($value)
		{
			$this->cert_verbiage = $value;
		}
		
		public function set_desc_short($value)
		{
			$this->desc_short = $value;
		}
		
		public function set_desc_title($value)
		{
			$this->desc_title = $value;
		}
		
		public function set_email_list($value)
		{
			$this->email_list = $value;
		}
		
		public function set_field_addroom($value)
		{
			$this->field_addroom = $value;
		}
		
		public function set_field_comments($value)
		{
			$this->field_comments = $value;
		}
		
		public function set_field_dept($value)
		{
			$this->field_dept = $value;
		}
		
		public function set_field_email($value)
		{
			$this->field_email = $value;
		}
		
		public function set_field_etrax($value)
		{
			$this->field_etrax = $value;
		}
		
		public function set_field_facility($value)
		{
			$this->field_facility = $value;
		}
		
		public function set_field_mail($value)
		{
			$this->field_mail = $value;
		}
		
		public function set_field_training_status($value)
		{
			$this->field_training_status = $value;
		}
		
		public function set_field_uk_status($value)
		{
			$this->field_uk_status = $value;
		}
		
		public function set_field_ukid($value)
		{
			$this->field_ukid = $value;
		}
		
		public function set_field_phone($value)
		{
			$this->field_phone = $value;
		}
		
		public function set_field_supervisor($value)
		{
			$this->field_supervisor = $value;
		}
		
		public function set_hidden($value)
		{
			$this->hidden = $value;
		}
		
		public function set_id($value)
		{
			$this->id = $value;
		}
		
		public function set_intro($value)
		{
			$this->intro = $value;
		}
		
		public function set_instr_head($value)
		{
			$this->instr_head = $value;
		}
		
		public function set_instr($value)
		{
			$this->instr = $value;
		}
		
		public function set_list_intro($value)
		{
			$this->list_intro = $value;
		}
		
		public function set_material_above($value)
		{
			$this->material_above = $value;
		}
		
		public function set_material_above_head($value)
		{
			$this->material_above_head = $value;
		}
		
		public function set_material_below($value)
		{
			$this->material_below = $value;
		}
		
		public function set_material_below_head($value)
		{
			$this->material_below_head = $value;
		}
		
		public function set_question_layout($value)
		{
			$this->question_layout = $value;
		}
		
		public function set_question_order($value)
		{
			$this->question_order = $value;
		}
		
		public function set_question_quantity($value)
		{
			$this->question_quantity = $value;
		}
		
		public function set_responsible_party($value)
		{
			$this->responsible_party = $value;
		}	
		
		public function set_log_create($value)
		{
			$this->log_create = $value;
		}
		
		public function set_log_update($value)
		{
			$this->log_update = $value;
		}
		
		public function set_log_update_by($value)
		{
			$this->log_update_by = $value;
		}	
		
		public function set_log_update_ip($value)
		{
			$this->log_update_ip = $value;
		}
		
		public function __construct($new_record = FALSE)
		{
			$this->new_record = $new_record;
			$this->set_defaults();
		}
		
		// Set default values for data members.
		private function set_defaults()
		{
			if($this->new_record === TRUE)
			{
				$this->id				= ITEM_ID::FRESH;
				$this->desc_title			= 'New Module, '.date(DATE_FORMAT);
				$this->instr_head			= 'Instructions';
				$this->instr				= 'Review the course material. When you are ready to take the exam answer all of the following questions, then click "Submit." When you "Submit" your registration and test, the results will be graded. You must answer 100% of the questions correctly to pass this course.';
				$this->field_comments		= TRUE;
				$this->field_dept			= TRUE;
				$this->field_uk_status		= TRUE;
				$this->hidden				= MODULE_ACCESS::HIDDEN;
				$this->question_order		= QUESTION_ORDER::RANDOM;
				$this->question_quantity	= CONSTANTS::QUANTITY;
				$this->question_layout		= QUESTION_LAYOUT::LISTED;
			}
		}
	}
	
?>
