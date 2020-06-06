<?php

	namespace dc\record_navigation;

	//require_once($_SERVER['DOCUMENT_ROOT'].'/libraries/php/classes/url_query/main.php'); 	// Page cache.

	abstract class RECORD_NAV_COMMANDS
	{		
		const DELETE			= 1;
		const FIRST				= 2;
		const LAST				= 3;
		const LISTING			= 4;
		const NEW_BLANK			= 5;
		const NEW_COPY			= 6;
		const NEXT				= 7;
		const PREVIOUS			= 8;
		const SAVE				= 9;		
	}
	
	class class_record_nav
	{		
		private			
			$markup			= NULL,
			$command		= NULL,			
			$action			= NULL,
			$fk_id			= NULL,
			$id				= NULL,
			$id_first		= NULL,
			$id_last		= NULL,
			$id_next		= NULL,
			$id_previous	= NULL,
			$dialog			= NULL;
			
		private
			$markup_cmd_delete		= NULL,
			$markup_cmd_first		= NULL,
			$markup_cmd_last		= NULL,
			$markup_cmd_new_blank	= NULL,
			$markup_cmd_new_copy	= NULL,
			$markup_cmd_next		= NULL,
			$markup_cmd_previous	= NULL,
			$markup_cmd_save		= NULL,
			$markup_cmd_save_block	= NULL;		
					
		public function __construct()
		{		
			$this->populate_from_request();	
		}
		
		public function populate_from_request()
		{
			// Interate through each class variable.
			foreach($this as $key => $value) 
			{			
				// If we can find a matching a post var with key matching
				// key of current object var, set object var to the post value. 
				if(isset($_REQUEST[$key]))
				{
					// Add 'set_' prefix so member name is now a mutator method name.
					$method = 'set_'.$key;
					
					// If a mutator method by the current name exists, run it and
					// pass current request value. 
					if(method_exists($this, $method)=== TRUE)
					{						
						$this->$method($_REQUEST[$key]);						
					}
				}
			}
		}
				
		// Create delete command markup.
		public function generate_command_delete()
		{	
			$result 	= NULL;
			$id			= NULL;
			$disabled 	= NULL;
			
			// Get id we'll be using.
			$id 		= $this->id;	
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::DELETE);
			
			if($this->id == DB_DEFAULTS::NEW_ID) $disabled = ' disabled';
				
			// Start caching.
			ob_start()
			
			?>
            <!-- Delete modal -->
            <div id="delete_<?php echo $this->id; ?>" class="modal fade" role="dialog">
                <div class="modal-dialog">                
                <!-- Modal content-->
                	<div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Confirm Delete</h4>
                        </div>
                        <div class="modal-body">
                            <p>If you delete this record it cannot be undone. Are you sure?</p>
                        </div>
                        <div class="modal-footer">                     
                            <a href="<?php echo $url_query->return_url_encoded(); ?>"
                                class		="btn btn-danger btn-responsive" 
                                
                                title		="Confirm delete."                                
                                ><span class="glyphicon glyphicon-trash"></span> Delete</a>
                            
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>                
                </div>
            </div><!-- #delete_<?php echo $this->id; ?> --> 
            
			<a href="#"
                    class		="btn btn-danger btn-responsive <?php echo $disabled ?>" 
                    data-toggle	="modal"
                    title		="Delete this record."
                    data-target	="#delete_<?php echo $this->id; ?>"
                    ><span class="glyphicon glyphicon-trash"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			$this->markup_cmd_delete = $result;
			
			return $result;
        }
		
		// Create first command markup.
		public function generate_command_first()
		{	
			$result 	= NULL;
			$id			= NULL;
			$disabled 	= 'disabled';
			$link		= '#';
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::FIRST);
		
		
			// Get id we'll be using.
			$id = $this->id_first;
		
			// If id is valid, construct a usable link.
			if($id)
			{
				$url_query->set_data('id', $id);
				$link = $url_query->return_url_encoded();			
				$disabled = NULL;
			}			
				
			// Start caching.
			ob_start()
			
			?>               
                <a href="<?php echo $link; ?>"                                                
                    class		="btn btn-primary btn-responsive <?php echo $disabled; ?>" 
                     
                    title		="Go to first record."
                    ><span class="glyphicon glyphicon-fast-backward"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Set data member.
			$this->markup_cmd_first = $result;
			
			// Output end result.
			return $result;
        }		
		
		// Create last command markup.
		public function generate_command_last()
		{	
			$result 	= NULL;
			$id			= NULL;
			$disabled 	= 'disabled';
			$link		= '#';
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::LAST);
			
			// Get id we'll be using.
			$id = $this->id_last;			
					
			// If id is valid, construct a usable link.
			if($id && $this->id != DB_DEFAULTS::NEW_GUID)
			{				
				$url_query->set_data('id', $id);
				$link = $url_query->return_url_encoded();			
				$disabled = NULL;
			}			
				
			// Start caching.
			ob_start()
			
			?>               
                <a href="<?php echo $link; ?>"                                                
                    class		="btn btn-primary btn-responsive <?php echo $disabled; ?>" 
                   
                    title		="Go to last record."
                    ><span class="glyphicon glyphicon-fast-forward"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Set data member.
			$this->markup_cmd_last = $result;
			
			// Output end result.
			return $result;
        }
		
		// Create list command markup.
		public function generate_command_list()
		{	
			$result 	= NULL;				
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::LISTING);
			
			// Start caching.
			ob_start()
			
			?>           
			<a href="<?php echo $url_query->return_url_encoded(); ?>"                        
                        class		="btn btn-info btn-responsive" 
                        
                        title		="Switch to list mode."
                        ><span class="glyphicon glyphicon glyphicon-list"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			$this->markup_cmd_last = $result;
			
			return $result;
        }
		
		// Create new (blank) command markup.
		public function generate_command_new_blank()
		{	
			$result 	= NULL;
			$disabled 	= NULL;
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::NEW_BLANK);
			$url_query->set_data('id', DB_DEFAULTS::NEW_ID);
			
			if($this->id == DB_DEFAULTS::NEW_ID) $disabled = ' disabled';
							
							
			// Start caching.
			ob_start()
			
			?>               
                <a href="<?php echo $url_query->return_url_encoded(); ?>"                        
                    class		="btn btn-success btn-responsive <?php echo $disabled; ?>" 
                     
                    title		="Start a new blank record."
                    ><span class="glyphicon glyphicon-plus"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Set data member.
			$this->markup_cmd_new_blank = $result;
			
			// Output end result.
			return $result;
        }
		
		// Create new (blank) command markup.
		public function generate_command_new_copy()
		{	
			$result 	= NULL;
			$disabled 	= NULL;
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::NEW_COPY);
			$url_query->set_data('id', DB_DEFAULTS::NEW_ID);
			
			//if($this->id == DB_DEFAULTS::NEW_ID) 
						$disabled = ' disabled';
			
			// Start caching.
			ob_start()
			
			?>                   
                <button 
                    type		="submit" 
                    name		="command"
                    id			="command_<?php echo RECORD_NAV_COMMANDS::NEW_COPY; ?>" 	
                    class		="btn btn-success btn-responsive <?php echo $disabled; ?>" 
                     
                    title		="Create a new copy of this record."
                    value		="<?php echo RECORD_NAV_COMMANDS::NEW_COPY; ?>"
                    formaction	="<?php echo $url_query->return_url_encoded(); ?>"
                    ><span class="glyphicon glyphicon-transfer"></span></button>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Set data member.
			$this->markup_cmd_new_copy = $result;
			
			// Output end result.
			return $result;
        }
		
		// Create next command markup.
		public function generate_command_next()
		{	
			$result 	= NULL;
			$id			= NULL;
			$disabled 	= 'disabled';
			$link		= '#';
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::NEXT);
			
			// Get id we'll be using.
			$id = $this->id_next;
		
			// If id is valid, construct a usable link.
			if($id)
			{
				$url_query->set_data('id', $id);
				$link = $url_query->return_url_encoded();		
				$disabled = NULL;
			}			
				
			// Start caching.
			ob_start()
			
			?>               
                <a href="<?php echo $link; ?>"                                                
                    class		="btn btn-primary btn-responsive <?php echo $disabled; ?>" 
                    title		="Go to next record."
                    ><span class="glyphicon glyphicon-forward"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Set data member.
			$this->markup_cmd_next = $result;
			
			// Output end result.
			return $result;
        }
		
		// Create previous command markup.
		public function generate_command_previous()
		{		
			$result 	= NULL;
			$id			= NULL;
			$disabled 	= 'disabled';
			$link		= '#';
			
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::PREVIOUS);
			
			// Get id we'll be using.
			$id = $this->id_previous;
		
			// If we're working on a new record, we'll act as if the new record is last in order,
			// so the previous button should go "back" to last exisiting record.
			if($this->id == DB_DEFAULTS::NEW_ID) $id = $this->id_last;			
		
			// If id is valid, construct a usable link.
			if($id)
			{				
				$url_query->set_data('id', $id);
				$link =	$url_query->return_url_encoded();	
				$disabled = NULL;
			}			
				
			// Start caching.
			ob_start()
			
			?>               
                <a href="<?php echo $link; ?>"                                                
                    class		="btn btn-primary btn-responsive <?php echo $disabled; ?>" 
                    title		="Go to previous record."
                    ><span class="glyphicon glyphicon-backward"></span></a>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Set data member.
			$this->markup_cmd_previous = $result;
			
			// Output end result.
			return $result;
        }
		
		// Create save command markup.
		public function generate_command_save()
		{	
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::SAVE);
			
			$result 	= NULL;
				
			// Start caching.
			ob_start()
			
			?>
			<button 
                    type		="submit" 
                    name		="command"                     	
                    class		="btn btn-warning btn-responsive" 
                    title		="Save this record."
                    value		="<?php echo RECORD_NAV_COMMANDS::SAVE; ?>"
                    formaction	="<?php echo $url_query->return_url_encoded(); ?>"
                    ><span class="glyphicon glyphicon-floppy-disk"></span></button>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			$this->markup_cmd_save = $result;
			
			return $result;
        }
		
		// Create save block command markup.
		public function generate_command_save_block()
		{	
			$url_query	= new url_query;
			$url_query->set_data('action', RECORD_NAV_COMMANDS::SAVE);			
			
			$result 	= NULL;
				
			// Start caching.
			ob_start()
			
			?>
			<button 
                    type		="submit"
                    name		="command"                    	
                    class		="btn btn-warning btn-block" 
                     
                    value		="<?php echo RECORD_NAV_COMMANDS::SAVE; ?>"
                    formaction	="<?php echo $url_query->return_url_encoded(); ?>"
                    ><span class="glyphicon glyphicon-floppy-disk"></span> Save This Item</button>
			<?php
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			$this->markup_cmd_save_block = $result;
			
			return $result;
        }
				
		// Generate and return record navigation list.
		public function generate_button_list($first = TRUE, $previous = TRUE, $new_blank = TRUE, $new_copy = TRUE, $save = TRUE, $list = TRUE, $delete = TRUE, $next = TRUE, $last = TRUE)
		{
			$result = NULL;
			
			// Start caching.
			ob_start()
			?>     
            <p><?php echo $this->dialog; ?></p>                       
                            
            <div class="btn-group" style="margin-top:5px; margin-bottom:15px;">
                       
                <?php
					if($first === TRUE) 	echo $this->generate_command_first();
					if($previous === TRUE)	echo $this->generate_command_previous();
					if($new_blank === TRUE)	echo $this->generate_command_new_blank();
					if($new_copy === TRUE)	echo $this->generate_command_new_copy();
					if($save === TRUE)		echo $this->generate_command_save();
					if($list === TRUE)		echo $this->generate_command_list();
					if($delete === TRUE)	echo $this->generate_command_delete();
					if($next === TRUE)		echo $this->generate_command_next();
					if($last === TRUE)		echo $this->generate_command_last();			
				?>                   
            </div>               
             
			<?php
			
			$this->generate_command_save_block();
			
			// Get cache contents and clean the cache.
			$result = ob_get_contents();
			ob_end_clean();	
			
			// Send results to data member.
			$this->markup = $result;
			
			return $result;
		}	
				
		// Accessors
		public function get_action()
		{
			return $this->action;
		}
		
		public function get_command()
		{
			return $this->command;
		}
		
		public function get_fk_id()
		{
			return $this->fk_id;
		}
		
		public function get_id()
		{
			return $this->id;
		}
		
		public function get_markup()
		{
			return $this->markup;
		}
		
		public function get_markup_cmd_save_block()
		{
			return $this->markup_cmd_save_block;
		}
		
		public function get_dialog()
		{
			return $this->dialog;
		}
		
		public function get_id_previous()
		{
			return $this->id_previous;
		}
			
		public function set_action($value)
		{			
			$this->action = $value;
		}
		
		public function set_command($value)
		{
			$this->command = $value;
		}
		
		public function set_fk_id($value)
		{
			$this->fk_id = $value;
		}
		
		public function set_id($value)
		{
			$this->id = $value;				
		}
		
		public function set_id_first($value)
		{
			$this->id_first = $value;
		}
		
		public function set_id_previous($value)
		{
			$this->id_previous = $value;
		}
		
		public function set_id_next($value)
		{
			$this->id_next = $value;
		}
		
		public function set_id_last($value)
		{
			$this->id_last = $value;
		}		
		
		public function set_dialog($value)
		{
			$this->dialog = $value;
		}
	}
	
?>
