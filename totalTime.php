<?php
	include 'dbInfo.php';
	
	$usr = trim($_GET["user"]);
	
	$stmt = $conn->prepare("SELECT SUM(duration) FROM User,Session WHERE name=?");
	$stmt->bind_param("s",$usr);
	if($stmt->execute()) {
		$stmt->bind_result($totalTime);
		$stmt->fetch();
		if($totalTime != null)
			$response = array('message' => $totalTime);
		else
			$response = array('message' => "wrong");
	}
	else
		$response = array('message' => "error");
	
	$stmt->close();
	$conn->close();
	print_r(json_encode($response));
?>