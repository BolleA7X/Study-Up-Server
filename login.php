<?php
	//formato json in input: {"username":"Alessio","password":"ciao"}

	include 'dbInfo.php';
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	
	$usr = trim($data['username']);
	$pwd = trim($data['password']);
	$response;
	
	$stmt = $conn->prepare("SELECT password FROM User WHERE name=?");
	$stmt->bind_param("s",$usr);
	if($stmt->execute()) {
		$stmt->store_result();
		$rows = $stmt->num_rows;
		$stmt->bind_result($hashedPwd);
		$stmt->fetch();
		if($rows == 1) {
			if(password_verify($pwd,$hashedPwd))
				$response = array('message' => "ok");
			else
				$response = array('message' => "wrong");
		}
		else
			$response = array('message' => "wrong");
		$stmt->close();
	}
	else
		$response = array('message' => "error");
	
	$conn->close();
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok"}
?>