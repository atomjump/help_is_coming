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
	if($argv[3]) { 		//This is the layer name
		//Set the global layer val, so that this is the correct database to delete this message on
		$_REQUEST['passcode'] = $argv[3];
	}
	
	if($argv[4]) {      //allow for a staging flag
	    $staging = true;
	}
	include_once($start_path . 'config/db_connect.php');	
	
    $define_classes_path = $start_path;     //This flag ensures we have access to the typical classes, before the cls.pluginapi.php is included
	require($start_path . "classes/cls.pluginapi.php");

    $api = new cls_plugin_api();

 
    if($argv[1] && $argv[2]) {
    
        sleep($argv[1]);
        $api->hide_message($argv[2], null);
    }
       
    
?>
