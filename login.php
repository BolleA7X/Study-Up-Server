<?php
	include 'dbInfo.php';
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	
	$usr = trim($data[0]);
	$pwd = trim($data[1]);
	
	$stmt = $conn->prepare("SELECT password FROM User WHERE username=?");
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
	}
	else
		$response = array('message' => "error");
	
	$stmt->close();
	$conn->close();
	print_r(json_encode($response));
?>