<?php
	// JQuery grabs programs list based on an agency_id
	require_once('includes/global.inc.php');
	global $configInfo; 
	global $db;
	

	$staff_arr = array();

	$sql = "SELECT * FROM staff WHERE 1 ORDER BY lastname,firstname";
		
	$result = $db->GetAll($sql);
		
	if($result) foreach($result as $person){
		$staff_arr[]=array("id"=>$person['id'],"lastname"=>$person['lastname'],"firstname"=>$person['firstname']);
	}

// encoding array to json format
echo json_encode($staff_arr);
	
?>
	