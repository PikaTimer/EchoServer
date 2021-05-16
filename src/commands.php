<?php

// Permit posting and confirming receipt of a command
// Commands are basically just "start, stop, and rewind"

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
	$command = json_decode(file_get_contents('php://input'), true );

	// Clear out any completed commands
	if (array_key_exists('completed', $command)) {
		$completed_id = $command['completed'];
		
		$clear_completed = $conn->prepare("DELETE from commands where id = :id");
		$clear_completed->bindParam(":id",$completed_id, PDO::PARAM_INT);
		$clear_completed->execute();
		
		header('Content-Type: text/plain; charset=utf-8');  
		echo "OK\n";
		exit;
	} else if (array_key_exists('command', $command)){
	
		$insert = $conn->prepare("INSERT into commands " .
						"(mac,command) " . 
						"VALUES (:mac,:command) " ) ;

		header('Content-Type: text/plain; charset=utf-8');  

		// bind the variables
		$mac = str_replace(":",'',$command['mac']); 
		$command = $command['command'];

		$insert->bindParam(":mac", $mac, PDO::PARAM_STR);
		$insert->bindParam(":command", $command, PDO::PARAM_STR);
	
		$insert->execute();
		
		echo "Submitted Command for " . $mac . " Command: " . $command . "\n\n";
		
		exit;
	} else if (array_key_exists('rename_location', $command)){
	
		$insert = $conn->prepare("UPDATE  readers set location=:location  " .
						"where mac = :mac" . 
						"VALUES (:mac,:location) " ) ;

		header('Content-Type: text/plain; charset=utf-8');  

		// bind the variables
		$mac = str_replace(":",'',$command['mac']); 
		$location = $command['location'];

		$insert->bindParam(":mac", $mac, PDO::PARAM_STR);
		$insert->bindParam(":location", $location, PDO::PARAM_STR);
	
		$insert->execute();
		
		echo "Renamed Location for " . $mac . " Location: " . $command . "\n\n";
		
		exit;
	} else if (array_key_exists('rename_reader', $command)){
	
		$insert = $conn->prepare("UPDATE  readers set name=:name  " .
						"where mac = :mac" . 
						"VALUES (:mac,:name) " ) ;

		header('Content-Type: text/plain; charset=utf-8');  

		// bind the variables
		$mac = str_replace(":",'',$command['mac']); 
		$name = $command['name'];

		$insert->bindParam(":mac", $mac, PDO::PARAM_STR);
		$insert->bindParam(":name", $name, PDO::PARAM_STR);
	
		$insert->execute();
		
		echo "Renamed Reader " . $mac . " Name: " . $command . "\n\n";
		
		exit;
	}
} else {
	
	header('Content-Type: text/plain; charset=utf-8');
	
	echo "Please select from the following options...";
	
}
?>