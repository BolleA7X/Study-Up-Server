<?php
	include 'dbInfo.php';
	
	$usr = trim($_GET["user"]);
	$list = array();
	$response;
	
	$stmt = $conn->prepare("SELECT SUM(duration) FROM Session WHERE user=?");
	$stmt->bind_param("s",$usr);
	if($stmt->execute()) {
		$stmt->bind_result($totalTime);
		$stmt->fetch();
		if($totalTime != null) {
			$response = array('message' => "ok", 'total time' => $totalTime);
			$stmt->close();
			$stmt = $conn->prepare("SELECT SUM(duration),course FROM Session WHERE user=? GROUP BY course");
			$stmt->bind_param("s",$usr);
			if($stmt->execute()) {
				$stmt->bind_result($time,$course);
				$i = 0;
				while($stmt->fetch()) {
					$list[$i] = array($course,$time);
					$i = $i + 1;
				}
				$response['courses'] = $list;	
				$stmt->close();
			}
			else
				$response = array('message' => "error");
				
		}
		else
			$response = array('message' => "wrong");
	}
	else
		$response = array('message' => "error");
	
	$conn->close();
	print_r(json_encode($response,JSON_PRETTY_PRINT));
?>