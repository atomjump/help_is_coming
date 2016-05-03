<?php

    if(!isset($help_is_coming_config)) {
        //Get global plugin config - but only once
        $data = file_get_contents (dirname(__FILE__) . "/config/config.json");
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

	$start_path = $help_is_coming_config['serverPath'];
	$notify = false;
	include_once($start_path . 'config/db_connect.php');	
	
    $define_classes_path = $start_path;     //This flag ensures we have access to the typical classes, before the cls.pluginapi.php is included
	require($start_path . "classes/cls.pluginapi.php");

    $api = new cls_plugin_api();

    if(isset($argv[1]) && isset($argv[2])) {
    
        $wait_seconds = $argv[1];      //Get from command line
        $message_id = $argv[2];
	

        sleep($wait_seconds);
        $api->hide_message($message_id);
    
    } else {
        error_log("Warning: You need to run help_is_coming/clear.php with a timeframe and message id.");        
    }


    
?>
