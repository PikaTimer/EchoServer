<?php

// If we have a post, then insert the data into the database
// If we have a get, then parse the parameters to pull the mac and (optional) start/end times and then dump the data in RFIDServer format


include 'config.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
	#Snag mac via $_SERVER['REQUEST_URI'] 

	/*
	timing_data:
	   id: varchar(14) <mac> + <LogID>
	   mac: varchar(6) <Six Char, all Uppercase>
	   chip: bigint(20) <whatever the chip # is>
	   time: bigint (20)  <YYYY-MM-DD HH:MM:SS> as seconds since epoc
	   milis: int(11)  (0 -> 999 in reality)
	   antenna: int(11)  <0, 1, 2, 3, or 4>
	   reader: int(11) <0, 1, or 2>
	   logID:  bigint(20) <incremental based on the reader>
	*/
	   


	$post_data = json_decode(file_get_contents('php://input'), true );

	// prep the insert string

		
	$insert = $conn->prepare("INSERT into timing_data " .
                                "(id,mac,chip,time,millis,antenna,reader,logid) " . 
                                "VALUES (:id,:mac,:chip,:timestamp,:millis,:antenna,:reader,:logid) " . 
                                "on duplicate key update timestamp = current_timestamp(6)") ;
					
		

	header('Content-Type: text/plain; charset=utf-8');  
	$count = 0;
	foreach($post_data as $key=>$value){

		$chip_read = json_decode($value,true);
		
		
		try {
			// bind the variables
			$mac = str_replace(":",'',$chip_read['mac']); 
			$logid = $chip_read['logNo']; 
			$chip = $chip_read['chip'];
			$reader= $chip_read['reader'];
			$antenna= $chip_read['port'];
                        
                        // fix the case when the millis is missing from the timestamp
                        if (! strpos($chip_read['timestamp'], ",") !== false) {
                            $chip_read['timestamp'] = $chip_read['timestamp'] . ".000";
                        }
                        
			list($time,$millis) = explode(".", $chip_read['timestamp']); 
                        
                        $timestamp = strtotime($time);
			$id = $mac . $logid;
				
			$insert->bindParam(":id", $id, PDO::PARAM_STR);
			$insert->bindParam(":mac", $mac, PDO::PARAM_STR);
			$insert->bindParam(":logid", $logid, PDO::PARAM_INT);
			$insert->bindParam(":chip", $chip, PDO::PARAM_INT);
			$insert->bindParam(":reader", $reader, PDO::PARAM_INT);
			$insert->bindParam(":antenna", $antenna, PDO::PARAM_INT);
			$insert->bindParam(":timestamp", $timestamp, PDO::PARAM_INT);
			$insert->bindParam(":millis", $millis , PDO::PARAM_STR); // STR since leading zeros are very important
		
		
			$insert->execute();
			$count++;
		} catch(Exception $e) {
			echo 'Exception on insert -> ';
			var_dump($e->getMessage());
			$insert->debugDumpParams();
		}		
	}
	echo "Uploaded " . $count . " reads\n";
	exit;
} else {
  
	// The server needs the following config line so we can snag the 
	// needed fields via the params option:
	// RewriteRule ^data/(.*) data.php?params=$1 [L,NC,NE]
	$params = array_filter(explode( "/", $_GET['params'] ));
	
	// We expect the following;
	// The keyword "from" and then a timestamp 
	// or 
	// 0 The Mac
	// 1 Start DateTime (optional)
	// 2 End DateTime (optional)
	
	// may as well tell them what they are about to receive
	//header('Content-Type: text/plain; charset=utf-8');
		
	if (!isset($params[0])) {
		header('Content-Type: text/plain; charset=utf-8');
		echo "Reader MAC not specified";
		exit;
	}
	
	if ($params[0] == "since" && isset($params[1] )) {
		
		$since = $params[1];
		
		try {
                    $select = $conn->prepare("SELECT mac,chip,time,millis,antenna,reader,timestamp from timing_data " .
                                                    "where timestamp > :since order by timestamp" ) ;
                    $select->bindParam(":since", $since, PDO::PARAM_STR);

                    $select->execute();

                    $results = $select->fetchAll();
                    //$count = 1;
                    $data = array();
                    foreach($results as $row){
                            //echo $count . "\n";
                            //$count++;
                            // Convert the timestamp in the database (epoc and milis) 
                            // to a format that the remote system expects: YYYY-MM-DD HH:MM:SS.sss
                            $dt = new DateTime("@" . $row['time']);   
                            $chiptime = $dt->format('Y-m-d H:i:s') . "." . $row['millis'];

                            // Output is the same as the old RFIDReader format of "reader, chip, bib,"time", reader, antenna" 
                            $rfid = $row['reader'] . "," . $row['chip'] . "," . $row['chip'] . ",\"" . $chiptime . "\"," . $row['reader'] . "," . $row['antenna'] ;
                            $data[] = array(
                                    "mac"=>$row['mac'],
                                    "posttime"=>$row['timestamp'],
                                    "chip" => $row['chip'],
                                    "time" => $row['time'],
                                    "millis" => $row['millis'],
                                    "rfid"=>$rfid
                            );
                    }

                    ## Response
                    $response = array(
                       "time_data" => $data
                    );

                    header('Content-Type: application/json; charset=utf-8');

                    echo json_encode($response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
			
		} catch(Exception $e) {
			echo 'Exception on select -> ';
			var_dump($e->getMessage());
			$select->debugDumpParams();
		}
	} else {
		header('Content-Type: text/plain; charset=utf-8');
		// uppercase and fix the mac
		$mac = substr(strtoupper(str_replace(":",'',$params[0])),0,6);
		
		
		// set the defaults
		$to = strtotime('tomorrow');
		$from = strtotime('today midnight');
		
		// The search query is looking for seconds since the epoc
		// strtotime will convert the human readable into unix time for us.
		
		// if we have an end time, attempt to parse it. 
		if (isset($params[2])) {
			if (($to = strtotime($params[2]))=== false) {
				$to = strtotime('tomorrow');
			} 
		}
		
		// if we have a start time, try and parse it too. 
		if (isset($params[1])){
			if (($from = strtotime($params[1]))=== false) {
				$from = strtotime('today midnight');
			} 
		}
			
		try {
			$select = $conn->prepare("SELECT chip,time,millis,antenna,reader from timing_data " .
                                                "where mac LIKE :mac AND time between :from AND :to" ) ;
			$select->bindParam(":mac", $mac, PDO::PARAM_STR);				
			$select->bindParam(":from", $from, PDO::PARAM_INT);
			$select->bindParam(":to", $to, PDO::PARAM_INT);				
				
			$select->execute();
			//echo ":from -> " . $from . "\n";
			//echo ":to -> " . $to . "\n";
			$results = $select->fetchAll();
			//$count = 1;
			foreach($results as $row){
				//echo $count . "\n";
				//$count++;
				// Convert the timestamp in the database (epoc and milis) 
				// to a format that the remote system expects: YYYY-MM-DD HH:MM:SS.sss
				$dt = new DateTime("@" . $row['time']);   
				$timestamp = $dt->format('Y-m-d H:i:s') . "." . $row['millis'];
				
				// Output is the same as the old RFIDReader format of "reader, chip, bib,"timestamp", reader, antenna" 
				echo $row['reader'] . "," . $row['chip'] . "," . $row['chip'] . ",\"" . $timestamp . "\"," . $row['reader'] . "," . $row['antenna'] . "\n";
			}
			
		} catch(Exception $e) {
			echo 'Exception on select -> ';
			var_dump($e->getMessage());
			$select->debugDumpParams();
		}
	}
	exit;
}
?>