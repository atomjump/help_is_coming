<?php
    include_once("classes/cls.pluginapi.php");

    class plugin_help_is_coming
    {
        public function on_message($message_forum_id, $message, $message_id, $sender_id, $recipient_id, $sender_name, $sender_email, $sender_phone)
        {
                      
        	if(!isset($help_is_coming_config)) {
                //Get global plugin config - but only once
                global $cnf;
                
                $path = dirname(__FILE__) . "/config/config.json";
                
                
	            $data = file_get_contents($path);
	            
                if($data) {
                    $help_is_coming_config = json_decode($data, true);
                    if(!isset($help_is_coming_config)) {
                        echo "Error: help_is_coming config/config.json is not valid JSON.";
                        exit(0);
                    }
                } else {
                    echo "Error: Missing config/config.json in help_is_coming plugin.";
                    exit(0);
                }
            }
            
            
            $api = new cls_plugin_api();
            
            //Loop through each of the forums
            $send = false;
            
            //Get the layer name, if available
            $layer_name = "";
            if(isset($_REQUEST['passcode'])) {
				$layer_name = $_REQUEST['passcode'];			
			}
		
			if(isset($_REQUEST['uniqueFeedbackId'])) {
				$layer_name = $_REQUEST['uniqueFeedbackId'];
			}
            
            
            
            $write_back = false;
            //Loop through our config file array
            for($cnt = 0; $cnt < count($help_is_coming_config['forums']); $cnt++) {
            
            	//Check if we are a scaled-up option
                $is_correct_database = true;	//Does any forum ID match the IDs of the current database. By default it does.
            	if(($help_is_coming_config['forums'][$cnt]['labelRegExp']) && ($layer_name != "")) {
            		//There is a different scaled-up database on this option
            		if(preg_match("/" . $help_is_coming_config['forums'][$cnt]['labelRegExp'] . "/",$layer_name, $matches) == true) {
            			//Yes, we have a regular expression match on the forum name. The ID is valid for this database (our database connection will already be switched to the regular expression's db).
            			$is_correct_database = true;
            		} else {
            			//The forum we're looking at here is referring to a different scaledUp database. (So the ID doesn't make any sense) 
            			$is_correct_database = false;
            		
            		}
            	}
            	
             
            
            
            	//Get the forum's ID
                if(isset($help_is_coming_config['forums'][$cnt]['forum_id'])) {
                	//The fast option id based method
                    $forum_id = $help_is_coming_config['forums'][$cnt]['forum_id'];
                } else {
                    if($help_is_coming_config['forums'][$cnt]['aj'] != 'default') {
                    	//It is a config file specified forum (and not a 'default' option)
                    
                    	if($is_correct_database == true) {
		                	//Normal option, need to get the forum id of this forum from the main db
		                	//and set it so that it is a faster check next time.
		                    $forum_info = $api->get_forum_id($help_is_coming_config['forums'][$cnt]['aj']);
		                    $forum_id = $forum_info['forum_id'];                    
		                    $help_is_coming_config['forums'][$cnt]['forum_id'] = $forum_id;
		                    $write_back = true;
		                } else {
		                	//We have no way of getting this forum's ID from the database, since it is a different database. Keep an unknown
		                	//forum_id for below.
		                	$forum_id = null;
		                
		                }
                    } else {
                    	//The database's default. Keep an unknown forum_id for below.
                        $forum_id = null;
                    }
                }
                //Have the forum's ID now...
                
                
              
               
                
                if(($message_forum_id == $forum_id)&&($is_correct_database == true)) {
                	//Yes, we're looking at the correct forum directly based on the ID
                	
                    //This forum has a wait time specifically for it
                    $timeframe = $help_is_coming_config['forums'][$cnt]['timeframe'];
                    $new_message = $help_is_coming_config['forums'][$cnt]['message'];
                    $helper = $help_is_coming_config['forums'][$cnt]['helperName'];
                    $helper_email = $help_is_coming_config['forums'][$cnt]['helperEmail'];
                    $come_back_within = $help_is_coming_config['forums'][$cnt]['comeBackWithin'];
                    $send = true;
                } else {
                	//We're looking at any other forum than the current message is on
                	
                	//Check if this is a 'default' option, so we set the fall-through.
                	if(($help_is_coming_config['forums'][$cnt]['aj'] == 'default')&&($is_correct_database == true)) {
                    		//Definitely a default option. And from the correct scaled up database. Use this message. The last
                    		//database listed correctly will be the one used (i.e. it will override the master database option)
		                    $timeframe = $help_is_coming_config['forums'][$cnt]['timeframe'];
		                    $new_message = $help_is_coming_config['forums'][$cnt]['message'];
		                    $helper = $help_is_coming_config['forums'][$cnt]['helperName'];
		                    $helper_email = $help_is_coming_config['forums'][$cnt]['helperEmail'];
		                    $come_back_within = $help_is_coming_config['forums'][$cnt]['comeBackWithin'];
		                    $send = true;
		                    
	                 }
                }
            }
            
           
            if($write_back == true) {
                //OK save back the config with the new forum ids in it - this is for speed. 
                $data = json_encode($help_is_coming_config, JSON_PRETTY_PRINT); //note this pretty print requires PHP ver 5.4
                file_put_contents($path, $data); 
            
            }
            
            if($help_is_coming_config['storeInDb'] == true) {
            	//Use the set of values from the database as an override to any config file specified version
                $sql = "SELECT var_help_is_coming_json FROM tbl_layer WHERE int_layer_id = " . $message_forum_id;
				$result = $api->db_select($sql);
				if($row = $api->db_fetch_array($result))
				{
            		if($row['var_help_is_coming_json']) {
            			//Ok not null
            			$forum_config = json_decode($row['var_help_is_coming_json']);
            			
            			//Get individual fields
            			$timeframe = $forum_config->timeframe;
						$new_message = $forum_config->message;
						$helper = $forum_config->helperName;
						$helper_email = $forum_config->helperEmail;
						$come_back_within = $forum_config->comeBackWithin;
						$send = true;
            		}
            
            	}
            }
            
            
            
            
           
            
            if($sender_email == $helper_email) {
                //Don't react to this message
            } else {
                if($helper_email != "") {
                
                     if(isset($_SESSION['help' . $sender_id . '_' . $message_forum_id])) {
                    
                        $now = time();
                        
                        $comeback_time = (intval($_SESSION['help' . $sender_id . '_' . $message_forum_id]) + intval($come_back_within));
                        
                        if($now < $comeback_time) {
                            //More than one day after we last posted e.g. $come_back_within = (60*60*24)
                            
                            //Update out last message posted date/time
                            //Store a session so that we know this sender sent a message in this forum already
                        	$now = time();
                        	$_SESSION['help' . $sender_id . '_' . $message_forum_id] = $now;
                             
                            //Exit early and don't send the user a message - the session should be updated by the surrounding script 
                            return true;
                        }
                    }
                
            
                    //React to this message, it was from another user
                    if($send == true) {
                        //Get the forum id
                                          
                        //Send a waiting message
                        $sender_ip = $api->get_current_user_ip();
                        
                        //Store a session so that we know this sender sent a message in this forum already
                        $now = time();
                        $_SESSION['help' . $sender_id . '_' . $message_forum_id] = $now; //Note this happens in 'parallel', and will wait for the parallel system call below
                        
                        $options = array('notification' => false);		//turn off any notifications from these messages
                        
                        
                        $new_message_id = $api->new_message($helper, $new_message, $sender_ip . ":" . $sender_id, $helper_email, $sender_ip, $message_forum_id, $options);
                        
                       
                        
                        
                        
                        //Now start a parallel process, that waits for a few seconds before removing the message        
                        $command = $help_is_coming_config['phpPath'] . " " . dirname(__FILE__) . "/clear.php " . $timeframe . " " . $new_message_id . " " . $layer_name;
                        global $staging;
                        if($staging == true) {
                            $command = $command . " staging";   //Ensure this works on a staging server  
                        }
                
                        
                        $api->parallel_system_call($command, "linux");
                        
                    }
                }
            } 

            return true;

        }
    }
?>
