<?php
    include_once("classes/cls.pluginapi.php");

    class plugin_help_is_coming
    {
        public function on_message($message_forum_id, $message, $message_id, $sender_id, $recipient_id, $sender_name, $sender_email, $sender_phone)
        {
            
            
        	if(!isset($help_is_coming_config)) {
                //Get global plugin config - but only once
                global $cnf;
                
                $path = $cnf['fileRoot'] . "plugins/help_is_coming/config/config.json";
                
	            $data = file_get_contents ($path);
	            
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
            
            
            
            
            //TODO: to speed this up, write back out the forum ids to the json file
            for($cnt = 0; $cnt < count($help_is_coming_config['forums']); $cnt++) {
            
                
                $forum_info = $api->get_forum_id($help_is_coming_config['forums'][$cnt]['aj']);
                
                
                
                if($message_forum_id == $forum_info['forum_id']) {
                    //Yep this forum has a wait time specifically for it
                    $timeframe = $help_is_coming_config['forums'][$cnt]['timeframe'];
                    $new_message = $help_is_coming_config['forums'][$cnt]['message'];
                    $helper = $help_is_coming_config['forums'][$cnt]['helperName'];
                    $helper_email = $help_is_coming_config['forums'][$cnt]['helperEmail'];
                    $send = true;
                } else {
                    if($help_is_coming_config['forums'][$cnt]['aj'] == 'default') {
                        $timeframe = $help_is_coming_config['forums'][$cnt]['timeframe'];
                        $new_message = $help_is_coming_config['forums'][$cnt]['message'];
                        $helper = $help_is_coming_config['forums'][$cnt]['helperName'];
                        $helper_email = $help_is_coming_config['forums'][$cnt]['helperEmail'];
                        $send = true;
                    }
                }
            }
            
           
            
            if($sender_email == $helper_email) {
                //Don't react to this message
            } else {
                if($helper_email != "") {
            
                    //React to this message, it was from another user
                    if($send == true) {
                        //Get the forum id
                       
                        
                    
                        //Send a waiting message
                        $new_message_id = $api->new_message($helper, $new_message, "", $helper_email, "192.168.1.1", $message_forum_id, false);
                        
                        //Now start a parallel process, that waits for a few seconds before removing the message
                        $command = $help_is_coming_config['serverPath'] . "plugins/help_is_coming/clear.php " . $timeframe . " " . $new_message_id;
                        
                        $api->parallel_system_call($help_is_coming_config['phpPath'] . " " . $command, "linux");
                        
                    }
                }
            } 

            return true;

        }
    }
?>
