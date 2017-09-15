<?php
	//formato json in input: {"data":[{"id":"1","duration":"30","year":"2017","month":"9","day":"13","th":"1","ex":"1","pr":"0","course":"Fisica"}, ... ],"user":"Bolle"}
	
	include 'dbInfo.php';
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	
	$response;
	$usr = trim($data['user']);
	$lastId = 0; $fail = false;
	
	$stmt = $conn->prepare("INSERT INTO Session (localId,duration,date,theory,exercise,project,user,course) VALUES (?,?,?,?,?,?,?,?)");
	
	if($usr != "") {
		$i = 0;
		foreach($data['data'] as $session) {
			$lastId = trim($session['id']);
			$duration = trim($session['duration']);
			$month = trim($session['month'])+1;
			$date = trim($session['year'])."-".$month."-".trim($session['day']);
			$th = trim($session['th']);
			$ex = trim($session['ex']);
			$pr = trim($session['pr']);
			$course = trim($session['course']);
			
			$stmt->bind_param("iisiiiss",$lastId,$duration,$date,$th,$ex,$pr,$usr,$course);
			if(!$stmt->execute()) {
				$response = array("message" => "error","index" => $lastId);
				$fail = true;
				break;
			}
			$i++;
		}
	}
	else {
		$fail = true;
		$response = array("message" => "error","index" => $lastId);
	}
	
	if(!$fail) {
		if($i > 0)
			$response = array("message" => "ok","index" => $lastId);
		else
			$response = array("message" => "alreadySynced","index" => $lastId);
	}
	
	$stmt->close();
	$conn->close();
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok","index":"3"}
?>