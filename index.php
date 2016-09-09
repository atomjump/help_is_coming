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
            
            
            
            
            
            $write_back = false;
            for($cnt = 0; $cnt < count($help_is_coming_config['forums']); $cnt++) {
            
                if(isset($help_is_coming_config['forums'][$cnt]['forum_id'])) {
                    $forum_id = $help_is_coming_config['forums'][$cnt]['forum_id'];
                } else {
                    if($help_is_coming_config['forums'][$cnt]['aj'] != 'default') {
                        $forum_info = $api->get_forum_id($help_is_coming_config['forums'][$cnt]['aj']);
                        $forum_id = $forum_info['forum_id'];                    
                        $help_is_coming_config['forums'][$cnt]['forum_id'] = $forum_id;
                        $write_back = true;
                    } else {
                        $forum_id = null;
                    }
                }
                
                
                if($message_forum_id == $forum_id) {
                    //Yep this forum has a wait time specifically for it
                    $timeframe = $help_is_coming_config['forums'][$cnt]['timeframe'];
                    $new_message = $help_is_coming_config['forums'][$cnt]['message'];
                    $helper = $help_is_coming_config['forums'][$cnt]['helperName'];
                    $helper_email = $help_is_coming_config['forums'][$cnt]['helperEmail'];
                    $come_back_within = $help_is_coming_config['forums'][$cnt]['comeBackWithin'];
                    $send = true;
                } else {
                    if($help_is_coming_config['forums'][$cnt]['aj'] == 'default') {
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
                            return true;
                        }
                    }
                
            
                    //React to this message, it was from another user
                    if($send == true) {
                        //Get the forum id
                                          
                        //Send a waiting message
                        $sender_ip = "192.168.1.1";     //This can be anything
                        
                        //Store a session so that we know this sender sent a message in this forum already
                        $now = time();
                        $_SESSION['help' . $sender_id . '_' . $message_forum_id] = $now; //Note this happens in 'parallel', and will wait fot the parallel system call below
                        
                        
                        
                        $new_message_id = $api->new_message($helper, $new_message, $sender_ip . ":" . $sender_id, $helper_email, $sender_ip, $message_forum_id, false);
                        
                       
                        
                        
                        
                        //Now start a parallel process, that waits for a few seconds before removing the message        
                        $command = $help_is_coming_config['phpPath'] . " " . dirname(__FILE__) . "/clear.php " . $timeframe . " " . $new_message_id;
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
