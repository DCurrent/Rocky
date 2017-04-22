

				<fieldset id="fs_moudle">
                	<legend>Training</legend>
                    
                    <p>Select the training module you would like to take, and press Go to begin.</p>
                    
                	<div class="form-group">
                            <label class="control-label col-sm-3" for="id">Module</label>
                            <div class="col-sm-9">
                                <select name="module" 
                                    id="module"
                                    class="form-control">
                                        <option value="">Select Module</option>
                                        
                                        <?php																
                                            if(is_object($_obj_data_list_module_list) === TRUE)
                                            {        
                                                // Generate table row for each item in list.
                                                for($_obj_data_list_module_list->rewind();	$_obj_data_list_module_list->valid(); $_obj_data_list_module_list->next())
                                                {	                                                               
                                                    $_obj_data_list_module = $_obj_data_list_module_list->current();
                                                                                                                            
                                                    $module_selected 	= NULL;
                                                    
                                                    // Is there is a current selection at all?
                                                    if($_module_parameters->get_id() != NULL)
                                                    {
                                                        // Does the current selection value = the ID of our current loop? 
                                                        if($_module_parameters->get_id() == $_obj_data_list_module->get_id())
                                                        {
                                                            // Set a NULL selection text variable with appropriate selected 
                                                            // markup to insert in the field.
                                                            $module_selected = ' selected ';
                                                        }								
                                                    }                                          
                                                    
                                                    ?>
                                                    <option value="<?php echo $_obj_data_list_module->get_id(); ?>" <?php echo $module_selected; ?>><?php echo $_obj_data_list_module->get_label(); ?></option>
                                                    <?php                                
                                                }
                                            }
                                        ?>                                    
                                                                         
                                </select>
                            </div>
                        </div>
                </fieldset>

