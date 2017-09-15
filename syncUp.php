<?php
	//formato json in input: {"data":[{"id":"1","duration":"30","year":"2017","month":"9","day":"13","th":"1","ex":"1","pr":"0","course":"Fisica"}, ... ],"user":"Bolle"}
	
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
	//variabili per memorizzare l'id locale (cioè nell'app) dell'ultima sessione inserita nel database sul server e flag per registrare successo o fallimento dell'operazione 
	$lastId = 0; $fail = false;
	
	//preparo la query per inserire la sessione
	$stmt = $conn->prepare("INSERT INTO Session (localId,duration,date,theory,exercise,project,user,course) VALUES (?,?,?,?,?,?,?,?)");
	
	//controllo che il campo dello username non sia vuoto, altrimenti non ha senso continuare
	if($usr != "") {
		//contatore per vedere quante sessioni sono state inserite
		$i = 0;
		//itero su ogni sessione
		foreach($data['data'] as $session) {
			//ottengo tutti i dati di una sessione
			$lastId = trim($session['id']);
			$duration = trim($session['duration']);
			//i mesi nell'app sono memorizzati come numeri da 0 a 11 mentre il database sul server li vuole da 1 a 12 => sommo 1
			$month = trim($session['month'])+1;
			//costruisco la stringa della data nel formato YYYY-MM-DD
			$date = trim($session['year'])."-".$month."-".trim($session['day']);
			$th = trim($session['th']);
			$ex = trim($session['ex']);
			$pr = trim($session['pr']);
			$course = trim($session['course']);
			
			$stmt->bind_param("iisiiiss",$lastId,$duration,$date,$th,$ex,$pr,$usr,$course);
			//ogni volta che voglio eseguire la query controllo che questa avvenga correttamente: se si verifica un errore interrompo l'operazione
			if(!$stmt->execute()) {
				//se fallisce una volta, mando il segnale di errore all'app insieme all'id dell'ultima sessione memorizzata
				$response = array("message" => "error","index" => $lastId);
				$fail = true;
				break;
			}
			$i++;
		}
	}
	//se il campo username è vuoto segnalo l'errore
	else {
		$fail = true;
		$response = array("message" => "error","index" => $lastId);
	}
	
	//se tutte le query hanno avuto successo
	if(!$fail) {
		//se il numero di sessioni da memorizzare (e che sono state memorizzate) è non nullo
		if($i > 0)
			//segnale di ok
			$response = array("message" => "ok","index" => $lastId);
		//se il numero di sessioni da memorizzare è 0 vuol dire che l'app non ha inviato dati perché i due database erano già sincronizzati
		else
			$response = array("message" => "alreadySynced","index" => $lastId);
	}
	
	//chiudo la connessione col database
	$stmt->close();
	$conn->close();
	//invio i risultati codificati in formato json all'app
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output: {"message":"ok","index":"3"}
?>