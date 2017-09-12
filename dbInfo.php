<?php
	$servername = "localhost";
	$dbUsername = "S4052357";
	$dbPassword = "sawproj.01";
	
	$conn = mysqli_connect($servername,$dbUsername,$dbPassword);
		if(!$conn)
			die("Connessione fallita ". mysqli_connect_error());
		mysqli_select_db($conn,$dbUsername);
?>