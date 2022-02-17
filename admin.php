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
	
	//Submitted a new or changed form
	if(isset($request['userform'])) {
		if($request['firstname'] != '' && $request['lastname'] != '' &&  $request['department_id'] != '0' && validateDate($request['start_date'])){
			if($request['is_manager']) $is_manager=1; else $is_manager=0;
			$firstname=$request['firstname'];
			$lastname=$request['lastname'];
			
			
			$call="
				firstname=". $db->qstr($firstname).",
				lastname=". $db->qstr($lastname).",
				department_id =$request[department_id],
				is_manager = $is_manager,
				start_date='$request[start_date]',
				week_hours='$request[week_hours]',
				comp_id='$request[comp_id]'
			WHERE id='$request[id]'";
			
			if($request['id']) {
				$sql="UPDATE admin SET $call WHERE id='$request[id]'";
			}
			else {
				$sql="INSERT INTO admin SET $call ";
			}
			
			$err = $sql;
			//$result=$db->Execute($sql);
			if(!$result) {$msg=$db->errorMsg();$err="Error updating:$sql ; $msg";}
			else {
				$activeUser=$db->GetRow("SELECT * FROM `users` WHERE comp_id='$request[comp_id]'");
			}
		} //field check
		

	}//isset userform
	else {
		//get the default user
		//$authUser=$db->GetRow("SELECT * FROM users WHERE comp_id='$configInfo[user]'");
	}


/////////////////////////////////////////////////////////	

$options=array();



if(isset($request['id'])){
	$job=$db->GetRow("SELECT * FROM jobs WHERE id=$request[id]");
	if($job){
		
	}
}



//prep the SELECT for the admin user 
$agencies=$db->GetAll("SELECT * FROM agencies WHERE 1 ORDER BY name");
if($agencies){
	foreach($agencies as $agency){
		if($job) if($agency['id']==$job['agency_id']) $sel="selected"; else $sel='';
		$options['agency'].="<option value='$agency[id]' $sel>$agency[name]</option>\r";
	}
}
$programs=$db->GetAll("SELECT * FROM programs WHERE agency_id=$job[agency_id] ORDER BY name");
if($programs){
	$options['program'].="<option value='0'></option>";
	foreach($programs as $program){
		if($job) if($program['id']==$job['program_id']) $sel="selected"; else $sel='';
		$options['program'].="<option value='$program[id]' $sel>$program[name]</option>\r";
	}
}

$people=$db->GetAll("SELECT * FROM faculty WHERE 1 ORDER BY lastname,firstname");
if($people){
	$options['people'].="<option value='0'></option>";
	foreach($people as $pi){
		if($job) if($pi['id']==$job['lead_id']) $sel="selected"; else $sel='';
		$options['people'].="<option value='$pi[id]' $sel>$pi[lastname], $pi[firstname]</option>\r";
	}
}

//ID Dropdown
$alljobs=$db->GetAll("SELECT * FROM jobs WHERE 1 ORDER BY id");
if($alljobs) foreach($alljobs as $onejob) {
	if($job) if($onejob['id']==$job['id']) $sel="selected"; else $sel='';
	$options['jobs'].="<option value='$onejob[id]' $sel>". $onejob['id']." | ".substr($onejob['title'],0,30) . "</option>/r";
}

//staff dropdown and list
$staff=$db->GetAll("SELECT * FROM staff WHERE 1 ORDER BY lastname,firstname");
if($staff){
	//print_r($staff);
	$options['staff'].="<option value='0'></option>";
	foreach($staff as $one){
		$options['staff'].="<option value='$one[id]' >$one[lastname], $one[firstname]</option>\r";
	}
	//parse staff list
	if($job){
		$sql="SELECT * FROM jobs_staff LEFT JOIN staff ON(jobs_staff.staff_id=staff.id) WHERE job_id=$job[id] ORDER BY staff.lastname";
		$stafflist=$db->GetAll($sql);
		
		if($stafflist) {
			$tbl='<table>';
			foreach($stafflist as $person) {
				$tbl.="<tr><td>".$person['lastname'].", ".$person['firstname']."</td><td>";
				$tbl.="<button type='button' onclick=\"document.getElementById('deletestaff').value='".$person['id']."';document.jobform.submit();\">Delete</button></td></tr>";
			}
			$tbl.='</table>';
			$options['stafflist']=$tbl;
		}	
	}
}

$stages=$db->GetAll("SELECT * FROM stages WHERE 1 ORDER BY name");
if($stages){
	$options['stages'].="<option value='0'></option>";
	foreach($stages as $one){
		$options['stages'].="<option value='$one[id]' >$one[name]</option>\r";
	}
	if($job){
		$stages=explode(',', $job['stages_ids']);
		if($stages){
			$tbl='<table>';
			foreach($stages as $one){
				//$err.=print_r($one,TRUE);
				$sql="SELECT * FROM stages WHERE id=$one";
				$stage=$db->GetRow($sql);
				if($stage){
					$tbl.="<tr><td>".$stage['name']."</td><td>";
					$tbl.="<button type='button' onclick=\"document.getElementById('deletestage').value='".$stage['id']."';document.jobform.submit();\">Delete</button></td></tr>";
				}
			}
			$tbl.='</table>';
			$options['stagelist']=$tbl;
		}
		
		
	}
}

$types=$db->GetAll("SELECT * FROM types WHERE 1 ORDER BY name");
if($types){
	$options['types'].="<option value='0'></option>";
	foreach($types as $one){
		$options['types'].="<option value='$one[id]' >$one[name]</option>\r";
	}
	if($job){
		$types=explode(',', $job['type_ids']);
		if($types){
			$tbl='<table>';
			foreach($types as $one){
				//$err.=print_r($one,TRUE);
				$sql="SELECT * FROM types WHERE id=$one";
				$type=$db->GetRow($sql);
				if($type){
					$tbl.="<tr><td>".$type['name']."</td><td>";
					$tbl.="<button type='button' onclick=\"document.getElementById('deletetype').value='".$type['id']."';document.jobform.submit();\">Delete</button></td></tr>";
				}
			}
			$tbl.='</table>';
			$options['typeslist']=$tbl;
		}
		
		
	}
}

$depts=$db->GetAll("SELECT departments.name as dept_name, faculties.name as faculty_name, departments.id as dept_id FROM departments left join faculties on(departments.fac_id = faculties.id) WHERE 1 ORDER BY dept_name");
if($depts){
	$options['dept_id'].="<option value='0'></option>\r";
	foreach($depts as $dept){
		
		if($job) if($dept['dept_id']==$job['dept_id']) {
			//$err=print_r($dept,true);
			$sel="selected"; 
			$job['faculty_n']=$dept['faculty_name'];
		}
		else $sel='';
		$options['dept_id'].="<option value='$dept[dept_id]' $sel>$dept[dept_name]</option>\r";
	}
	
}

$nom=$db->GetAll("SELECT * FROM faculty WHERE 1 ORDER BY lastname,firstname");
if($nom){
	$options['nominator_id'].="<option value='0'></option>";
	foreach($nom as $pi){
		if($job) if($pi['id']==$job['nominator_id']) $sel="selected"; else $sel='';
		$options['nominator_id'].="<option value='$pi[id]' $sel>$pi[lastname], $pi[firstname]</option>\r";
	}
}

$options['degree_options']="<option value='null'></option>\r";
foreach(array('Low','Medium','High') as $degree){
	if($degree==$job['degree']) $sel='selected'; else $sel='';
	$options['degree_options'].="<option value='$degree' $sel>$degree</option>\r";
}



 
 //$err=print_r($options,true);
echo $template->render([
	'authuser'=>$authUser,
	'options'=>$options,
	'job'=>$job,
	'config'=>$configInfo,
	'pagename'=>'admin',
	'title'=>'Job Administration',
	'err'=>$err]);
	
	
	function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}
?>
  

	
	
	


