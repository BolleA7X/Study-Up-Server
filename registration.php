<?php
	//formato json in input: {"username":"Alessio","password":"ciao"}

	include 'dbInfo.php';
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	
	$usr = trim($data['username']);
	$pwd = password_hash(trim($data['password']),PASSWORD_DEFAULT);
	$response;
	
	$stmt = $conn->prepare("SELECT * FROM User WHERE name=?");
	$stmt->bind_param("s",$usr);
	if($stmt->execute()) {
		$stmt->store_result();
		$rows = $stmt->num_rows;
		$stmt->close();
		if($rows == 0) {
			$stmt = $conn->prepare("INSERT INTO User (name,password) VALUES (?,?)");
			$stmt->bind_param("ss",$usr,$pwd);
			if($stmt->execute()) {
				$response = array('message' => "ok");
				$stmt->close();
			}
			else
				$response = array('message' => "error");
		}
		else
			$response = array('message' => "taken");
	}
	else
		$response = array('message' => "error");
	
	$conn->close();
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok"}
?>