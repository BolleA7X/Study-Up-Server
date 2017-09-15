<?php
	//formato json in input: {"username":"Alessio","password":"ciao"}

	//includo le informazioni per connettersi al db
	include 'dbInfo.php';
	
	//preparo la variabile per ottenere l'input in formato json dall'app
	$json = file_get_contents('php://input');
	//decodifico il json memorizzandone i dati in un array set-associativo
	$data = json_decode($json,true);
	
	//ottengo i dati dal json (la password la cripto subito)
	$usr = trim($data['username']);
	$pwd = password_hash(trim($data['password']),PASSWORD_DEFAULT);
	//variabile per memorizzare la risposta del server che verrà inviata all'app
	$response;
	
	//preparo la query per vedere se lo username è già stato preso
	$stmt = $conn->prepare("SELECT * FROM User WHERE name=?");
	$stmt->bind_param("s",$usr);
	//eseguo la query: se ha successo
	if($stmt->execute()) {
		$stmt->store_result();
		//memorizzo il numero di righe
		$rows = $stmt->num_rows;
		$stmt->close();
		//se il numero di righe è 0 non esistono utenti con quel nome
		if($rows == 0) {
			//allora inserisco nome utente e password
			$stmt = $conn->prepare("INSERT INTO User (name,password) VALUES (?,?)");
			$stmt->bind_param("ss",$usr,$pwd);
			//se l'inserimento va a buon fine la risposta del server è positiva
			if($stmt->execute()) {
				$response = array('message' => "ok");
				$stmt->close();
			}
			//altrimenti c'è stato un errore
			else
				$response = array('message' => "error");
		}
		//altrimenti lo username è già in uso
		else
			$response = array('message' => "taken");
	}
	//altrimenti c'è stato un errore
	else
		$response = array('message' => "error");
	
	//chiudo la connessione al database
	$conn->close();
	//invio i risultati codificati in formato json all'app
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok"}
?>