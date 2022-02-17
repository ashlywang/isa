<?php
	require_once('includes/global.inc.php'); 
	global $configInfo; 
	global $db;
	global $authUser;	
	
	//echo"<pre>";
	//print_r($_SESSION); 
	//echo "</pre>";
	$template = $twig->load('admin.twig');
	
	if(sizeof($_REQUEST) >= 1) {
		//clean up
		$request=array();
		foreach($_REQUEST as $key=>$item){
			$request[$key]=filter_var($item,FILTER_SANITIZE_STRING, array('options'=>'FILTER_FLAG_NO_ENCODE_QUOTES'));
		}
	}//any post variables
	
	//To keep this clear:
	//$configInfo[user] comes in as the logged in user every time the form loads. 
	//It can be overridden by the adminform - which now loads the other person
	//The userform carries a comp_id with it as well. It needs to be carried forward into the next load
	
	$activeUser=$authUser;
	
	//Is this an overide? 
	if(isset($request['adminform']) && isset($request['comp_id'])) {
		if($request['comp_id']=='-1'){
			//new user
			$result=$db->Execute("	INSERT INTO users SET
									firstname='',
									lastname='',
									department_id =0,
									is_manager = 0,
									start_date='2020-08-16',
									comp_id=''");
			if(!$result) {$msg=$db->errorMsg();$err="Error inserting:$sql ; $msg";}
			else {
				$id=$db->insert_id();
				$requestedUser=$db->GetRow("SELECT * FROM `users` WHERE id='$id'");
				if($requestedUser) $activeUser = $requestedUser;
			}
		}
		else {
			$requestedUser=$db->GetRow("SELECT * FROM `users` WHERE comp_id='$request[comp_id]'");
			if($requestedUser) $activeUser = $requestedUser;	
		}	
	}	
	elseif(isset($request['userform'])) {
		if($request['firstname'] != '' && $request['lastname'] != '' &&  $request['department_id'] != '0' && validateDate($request['start_date'])){
			if($request['is_manager']) $is_manager=1; else $is_manager=0;
			$firstname=$request['firstname'];
			$lastname=$request['lastname'];
			
				$sql="UPDATE users SET
				firstname=". $db->qstr($firstname).",
				lastname=". $db->qstr($lastname).",
				department_id =$request[department_id],
				is_manager = $is_manager,
				start_date='$request[start_date]',
				week_hours='$request[week_hours]',
				comp_id='$request[comp_id]'
			WHERE id='$request[id]'";
			$result=$db->Execute($sql);
			if(!$result) {$msg=$db->errorMsg();$err="Error updating:$sql ; $msg";}
			else {
				$activeUser=$db->GetRow("SELECT * FROM `users` WHERE comp_id='$request[comp_id]'");
			}
		} //field check
		else {
			$err="Please enter all fields correctly";
			$activeUser['firstname']=$request['firstname'];
			$activeUser['lastname']=$request['lastname'];
			$activeUser['department_id'] =$request['department_id'];
			$activeUser['is_manager'] = $request['is_manager'];
			$activeUser['comp_id'] = $request['comp_id'];
			$activeUser['start_date'] = $request['start_date'];
			$activeUser['week_hours'] = $request['week_hours'];
			$activeUser['id'] = $request['id'];

		}
		//if this is an admin user then the user_id can be changed
		//if(isset($request['comp_id'])) $configInfo['user']=$request['user_id'];
	}//isset userform
	else {
		//get the default user
		//$authUser=$db->GetRow("SELECT * FROM users WHERE comp_id='$configInfo[user]'");
	}


/////////////////////////////////////////////////////////	




//prep the SELECT for the admin user 
$users=$db->GetAll("SELECT * FROM users WHERE 1 ORDER BY lastname,firstname");
if($users){
	foreach($users as $user){
		if($user['id']==$activeUser['id']) $sel="selected"; else $sel='';
		$adminform_options.="<option value='$user[comp_id]' $sel>$user[lastname], $user[firstname]</option>\r";
	}
}


$sql="SELECT * from departments ORDER BY name";
$departments=$db->GetAll($sql);
$doptions="<option value='0'></option>\r";
foreach($departments as $department){
	if($department['id']==$activeUser['department_id']) $sel="Selected"; else $sel='';
	$doptions.="<option value='$department[id]' $sel>$department[name]</option>\r";
}

$activeUser['is_manager']= ($activeUser['is_manager']) ? 'checked':'';
 
$activeUser['firstname']=html_entity_decode($activeUser['firstname'],ENT_QUOTES);
$activeUser['lastname']=html_entity_decode($activeUser['lastname'],ENT_QUOTES)	;
if(is_null($activeUser['start_date'])) $activeUser['start_date']='2000-01-01';
 
 
 
 
echo $template->render([
	'authuser'=>$authUser,
	'activeUser'=>$activeUser,
	'adminform_options'=>$adminform_options,
	'doptions'=>$doptions,
	'config'=>$configInfo,
	'pagename'=>'admin',
	'title'=>'User Administration',
	'err'=>$err]);
	
	
	function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
?>
  

	
	
	


