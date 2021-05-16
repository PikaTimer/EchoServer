<?php

// Replace the following with the correct values for your environment
$server = "SERVER";
$username = "USERNAME";
$password = "PASSWORD";
$dbname = "DBNAME";



// Nothing below here should need to be modified. 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// We will normalize everything based on UTC. 
// This keeps strtotime from trying to translate between the EPOC (in UTC) and 
// wherever the server happens to be (which is never where we are)
date_default_timezone_set("UTC"); 


// Create connection
try{
   $conn = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8","$username","$password");
   $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
   $conn->exec("SET time_zone = 'GMT'");
}catch(PDOException $e){
   die('Unable to connect with the database');
}

/*

timing_data:
   id: <mac> + <LogID>
   mac: <Six Char, all Uppercase>
   chip:  <whatever the chip # is>
   time:  <raw seconds since 1/1/1980>
   milis: <000 to 999> 
   antenna: <0, 1, 2, 3, or 4>
   reader: <0, 1, or 2>
   logID:  <incremental based on the reader>
   
Readers
   mac: <key>
   last_update:    date/timestamp
   reader_status:  Boolean Reading or not
   battery_status:  Integer from 0 to 100 % battery
   
Commands:
   Mac:  <Reader MAC>
   Command: <string w/ the encoded command like "start" or "restart" or "rewind since xxxx">
   
*/
?>