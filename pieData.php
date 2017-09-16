<?php
	//formato json in input: {"user":"Bolle"}

	//includo le informazioni per connettersi al db
	include 'dbInfo.php';
	
	//preparo la variabile per ottenere l'input in formato json dall'app
	$json = file_get_contents('php://input');
	//decodifico il json memorizzandone i dati in un array set-associativo
	$data = json_decode($json,true);
	
	//ottengo lo username dal json
	$usr = trim($data["user"]);
	//variabile per memorizzare i dati di ogni sessione dell'utente selezionato
	$list = array();
	//variabile per memorizzare la risposta del server che verrà inviata all'app
	$response;
	
	//preparo la query per ottenere la somma totale delle durate di ogni sessione dell'utente scelto
	$stmt = $conn->prepare("SELECT SUM(duration) FROM Session WHERE user=?");
	$stmt->bind_param("s",$usr);
	//se la query ha successo
	if($stmt->execute()) {
		$stmt->bind_result($totalTime);
		$stmt->fetch();
		//se ottengo un risultato (ovvero ho trovato l'utente e calcolato la somma di tutte le durate)
		if($totalTime != null) {
			$response = array('message' => "ok", 'total_time' => $totalTime);
			$stmt->close();
			//preparo la query per ottenere il tempo totale associato ad ogni suo corso
			$stmt = $conn->prepare("SELECT SUM(duration),course FROM Session WHERE user=? GROUP BY course");
			$stmt->bind_param("s",$usr);
			//se la query ha successo
			if($stmt->execute()) {
				$stmt->bind_result($time,$course);
				$i = 0;
				while($stmt->fetch()) {
					//memorizzo corso e tempo associato ad esso, iterando su tutti i suoi corsi, in un array
					$list[$i] = array('course' => $course,'time' => $time);
					$i = $i + 1;
				}
				//nella risposta del server inserisco la lista di coppie (corso,durata) contenute nell'array
				$response['courses'] = $list;	
				$stmt->close();
			}
			//altrimenti c'è stato un errore
			else
				$response = array('message' => "error");
			//dopo aver ottenuto le informazioni sui corsi calcolo le percentuali per i tipi di sessione
			$stmt = $conn->prepare("SELECT SUM(theory),SUM(exercise),SUM(project) FROM Session WHERE user=?");
			$stmt->bind_param("s",$usr);
			//se la query ha successo
			if($stmt->execute()) {
				$stmt->bind_result($th,$ex,$pr);
				$stmt->fetch();
				//dato che ci possono essere sessioni con tipi di sessione multipli (es: sia teoria che esercizi) la somma delle percentuali può essere superiore a 100, quindi
				//calcolo il coefficiente di normalizzazione
				$k = 1/($th+$ex+$pr);
				//calcolo le percentuali normalizzate
				$th_n = $k * $th * 100;
				$ex_n = $k * $ex * 100;
				$pr_n = $k * $pr * 100;
				$percents = array('th' => $th_n,'ex' => $ex_n,'pr' => $pr_n);
				$response['percents'] = $percents;
				$stmt->close();
			}
			//altrimenti c'è stato un errore
			else
				$response = array('message' => "error");
		}
		//altrimenti lo username è sbagliato
		else
			$response = array('message' => "wrong");
	}
	//altrimenti c'è stato un errore
	else
		$response = array('message' => "error");
	
	//chiudo la connessione col database
	$conn->close();
	//invio i risultati codificati in formato json all'app
	print_r(json_encode($response,JSON_PRETTY_PRINT));
	
	//formato json in output (se corretto): {"message":"ok","total_time":"1280","courses":[{"course":"Fisica","time":"470"}, ... ],"percents":["th":"50","ex":"45","pr":"5"]}
	//formato json in output (se sbagliato/errore): {"message":"wrong"} / {"message":"error"}
?>