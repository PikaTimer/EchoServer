# Echo Server
Simple php/mysql server for the EchoTransmitter and EchoReceiver apps

# Requirements
* PHP 7.x or newer
* MySQL DB 5.4 or newer

# Setup

1. Use your hosting providers tools to create a MySQL database
2. Use PHPMySQL or similar to execute the echo_db.sql file to initialize the database
3. Edit the config.php file and update the server, username, password, and database fields with the values required to connect to your database
4. Use EchoTransmitter to send data to the server. See https://github.com/PikaTimer/EchoTransmitter/
5. Use EchoReceiver to read the data from the server. See https://github.com/PikaTimer/EchoReceiver/

# Care and Feeding
The timing_data table will grow over time. You will want to truncate it after every race weekend once you have retrieved all of the data from your readers. 

# Support
There is no support provided for this application. It is free software, free as in puppy. 

# Notices
* This is a pre-release. The scripts probably have security holes in them. 
* There is no password protection at this time. 
* Treat the location of your EchoServer as a secret. 

# TODO
* Better input sanitization
* Password Protect key functions
* Include a Setup script to load the DB
* Include a maintenance page to truncate the timing_data table


