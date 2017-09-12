<?php
	include 'dbInfo.php';
	
	$json = file_get_contents('php://input');
	$data = json_decode($json,true);
	
	$usr = trim($data[0]);
	$pwd = password_hash(trim($data[1]),PASSWORD_DEFAULT);
	
	$stmt = $conn->prepare("SELECT * FROM Users WHERE name=?");
	$stmt->bind_param("s",$usr);
	if($stmt->execute()) {
		$stmt->store_result();
		$rows = $stmt->num_rows;
		$stmt->close();
		if($rows == 0) {
			$stmt = $conn->prepare("INSERT INTO Users (name,password) VALUES (?,?)");
			$stmt->bind_param("ss",$usr,$pwd);
			if($stmt->execute()) {
				$response = array('message' => "ok");
			}
			else
				$response = array('message' => "error");
		}
		else
			$response = array('message' => "taken");
	}
	else
		$response = array('message' => "error");
	
	$stmt->close();
	$conn->close();
	print_r(json_encode($response));
?>