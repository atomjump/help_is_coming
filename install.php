<?php
	if(!isset($help_is_coming_config)) {
        //Get global plugin config - but only once
		$path = dirname(__FILE__) . "/config/config.json";
		echo "Searching " . $path;
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
 
	$start_path = $help_is_coming_config['serverPath'];
	
	$staging = $help_is_coming_config['staging'];
	$notify = false;
	include_once($start_path . 'config/db_connect.php');	
	echo "Start path:" . $start_path . "\n";

	
	$define_classes_path = $start_path;     //This flag ensures we have access to the typical classes, before the cls.pluginapi.php is included
	
	echo "Classes path:" . $define_classes_path . "\n";
	
	require($start_path . "classes/cls.pluginapi.php");
	
	$api = new cls_plugin_api();
	
	//Insert a column into the user table - a free text json for the config on a per layer (forum) basis
	$sql = "ALTER TABLE tbl_layer ADD COLUMN `var_help_is_coming_json` varchar(2000) DEFAULT NULL";
	echo "Updating user table. SQL:" . $sql . "\n";
	$result = $api->db_select($sql);
	echo "Completed.  Make sure you set storeInDb as 'true' in your config/config.json file, to allow these settings per forum to be stored in the database, rather than just the config.\n";
	

?>