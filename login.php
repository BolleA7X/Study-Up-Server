<?php
	//formato json in input: {"username":"Alessio","password":"ciao"}

	//includo le informazioni per connettersi al db
	include 'dbInfo.php';
	
	//preparo la variabile per ottenere l'input in formato json dall'app
	$json = file_get_contents('php://input');
	//decodifico il json memorizzandone i dati in un array set-associativo
	$data = json_decode($json,true);
	
	//ottengo i dati dal json
	$usr = trim($data['username']);
	$pwd = trim($data['password']);
	//variabile per memorizzare la risposta del server che verrà inviata all'app
	$response;
	
	//preparo la query per ottenere la password criptata dal db a partire dallo username inserito
	$stmt = $conn->prepare("SELECT password FROM User WHERE name=?");
	$stmt->bind_param("s",$usr);
	//se la query ha successo
	if($stmt->execute()) {
		$stmt->store_result();
		$rows = $stmt->num_rows;
		$stmt->bind_result($hashedPwd);
		$stmt->fetch();
		//se trovo lo username
		if($rows == 1) {
			//controllo se la password è corretta
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
		//altrimenti c'è stato un errore
		$response = array('message' => "error");
	
	//chiudo la connessione col database
	$conn->close();
	//invio i risultati codificati in formato json all'app
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok"}
?>