<?php
	// JQuery grabs programs list based on an agency_id
	require_once('includes/global.inc.php');
	global $configInfo; 
	global $db;
	
	$agency_id=0;
	
	if(isset($_REQUEST['agency'])){
	$agency_id = filter_var($_REQUEST['agency'],FILTER_SANITIZE_STRING, array('options'=>'FILTER_FLAG_NO_ENCODE_QUOTES'));
	}

	$programs_arr = array();

	if($agency_id > 0){
		$sql = "SELECT id,name FROM programs WHERE agency_id=".$agency_id. " ORDER BY name";
		
		$result = $db->GetAll($sql);
		
		if($result) foreach($result as $program){
			$programs_arr[]=array("id"=>$program['id'],"name"=>$program['name']);
		}
	}
// encoding array to json format
echo json_encode($programs_arr);
	
?>
	