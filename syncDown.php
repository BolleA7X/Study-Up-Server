<?php
	//formato json in input: {"user":"Bolle"}
	
	//includo le informazioni per connettersi al db
	include 'dbInfo.php';
	
	//preparo la variabile per ottenere l'input in formato json dall'app
	$json = file_get_contents('php://input');
	//decodifico il json memorizzandone i dati in un array set-associativo
	$data = json_decode($json,true);
	
	//variabile per memorizzare la risposta del server che verrà inviata all'app
	$response;
	//ottengo lo username dal json
	$usr = trim($data['user']);
	
	//preparo la query per recuperare i dati delle sessioni dell'utente
	$stmt = $conn->prepare("SELECT duration,date,theory,exercise,project,course FROM Session WHERE user=?");
	$stmt->bind_param("s",$usr);
	//se la query ha successo
	if($stmt->execute()) {
		$stmt->bind_result($duration,$date,$th,$ex,$pr,$course);
		//itero sulle righe
		$i = 0; $list = array();
		while($stmt->fetch()) {
			$id = $i+1;
			$year = substr($date,0,4);
			$month = substr($date,5,2) - 1;
			$day = substr($date,8,2);
			//dopo aver ottenuto i dati per questa riga, li memorizzo un un array
			$list[$i] = array('id' => $id,'duration' => $duration,'year' => $year,'month' => $month,'day' => $day,'th' => $th,'ex' => $ex,'pr' => $pr,'course' => $course);
			$i++;
		}
		//se il numero di righe è non nullo allora ci sono dati sull'utente sul server
		if($i != 0)
			$response = array('message' => "ok",'data' => $list,'user' => $usr,'lastId' => $i);
		else
			$response = array('message' => "noData");
	}
	//altrimenti c'è stato un errore
	else
		$response = array('message' => "error");
	
	//chiudo la connessione col database
	$stmt->close();
	$conn->close();
	//invio i risultati codificati in formato json all'app
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok","data":[{"id":"1","duration":"30","year":"2017","month":"9","day":"13","th":"1","ex":"1","pr":"0","course":"Fisica"}, ... ],"user":"Bolle","lastId":"5"}
?>