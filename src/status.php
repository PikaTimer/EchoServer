<?php

// Handle reader status updates
// If updating a status, also check 
// for any pending commands and ship them back

include 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
	$status = json_decode(file_get_contents('php://input'), true );

	// prep the insert string
	$insert = $conn->prepare("INSERT into readers " .
                                "(mac,reader_status,battery_status,last_update) " . 
                                "VALUES (:mac,:reading,:battery,utc_timestamp()) " . 
                                "on duplicate key update reader_status = :reading, battery_status = :battery, last_update = utc_timestamp()	" ) ;

	try {
		// bind the variables
		$mac = str_replace(":",'',$status['mac']); 
		$reading = $status['reading'];
		$battery = $status['battery'];

			
		$insert->bindParam(":mac", $mac, PDO::PARAM_STR);
		$insert->bindParam(":battery", $battery, PDO::PARAM_INT);
		$insert->bindParam(":reading", $reading, PDO::PARAM_BOOL);
	
	
		$insert->execute();
		
		$commands = $conn->prepare("SELECT * from commands where mac LIKE :mac");	
	
		$commands->bindParam(":mac", $mac, PDO::PARAM_STR);
		$commands->execute();
		
		$data = array();
		foreach($commands->fetchAll() as $row){
		   $data[] = array(
				"id" => $row['id'],
				"command"=>$row['command'] 
		   );
		}

		## Response
		$response = array(
		   "Status" => "Updated Status for " . $mac,
		   "Commands" => $data
		);

		header('Content-Type: application/json; charset=utf-8');

		echo json_encode($response, JSON_UNESCAPED_UNICODE);
	
		
		
	} catch(Exception $e) {
		echo 'Exception on insert -> ';
		var_dump($e->getMessage());
		$insert->debugDumpParams();
	}

	//echo "Updated Status for " . $mac . " Battery: " + $battery + " Reading: " + $reading + "\n\n";
	
	exit;
} else {
  
	// The server needs the following config line so we can snag the 
	// needed fields via the params option:
	// RewriteRule ^data/(.*) data.php?mac=$1 [L,NC,NE]
	
	// If we see a mac, report on just the mac
	// otherwise, dump all of them as a json array 
		
	if (!array_key_exists("mac",$_GET)) {
		$select = $conn->prepare("SELECT mac,name,location,last_update,reader_status,battery_status  FROM readers" ) ;
		$select->execute();
			
		$results = $select->fetchAll();
		
		$data = array();
		foreach($results as $row){
			$status = $row['reader_status'] == "0"?'false':'true';
		    $data[] = array(
			  "mac"=>$row['mac'],
			  "name"=>$row['name'],
			  "location"=>$row['location'],
			  "battery" => $row['battery_status'],
			  "reading" => $status,
			  "updated" => $row['last_update']
		   );
		}

		## Response
		$response = array(
		   "readers" => $data
		);

		header('Content-Type: application/json; charset=utf-8');

		echo json_encode($response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		
		exit;
	} else {
		// uppercase and fix the mac
		$params = array_filter(explode( "/", $_GET['mac'] ));
		$mac = substr(strtoupper(str_replace(":",'',$params[0])),0,6);
		
		
			
		try {
			$select = $conn->prepare("SELECT last_update,reader_status,battery_status  FROM readers WHERE mac = :mac " ) ;
			$select->bindParam(":mac", $mac, PDO::PARAM_STR);
				
			$select->execute();
			
			$result = $select->fetch();
			$response = array(
			   "mac" => $mac,
			   "battery" => $result['battery_status'],
			   "reading" => $result['reader_status'],
			   "updated" => $result['last_update']
			);

			header('Content-Type: application/json; charset=utf-8');

			echo json_encode($response, JSON_UNESCAPED_UNICODE);
			
		} catch(Exception $e) {
			echo 'Exception on select -> ';
			var_dump($e->getMessage());
			$select->debugDumpParams();
		}
		exit;
	}
}

