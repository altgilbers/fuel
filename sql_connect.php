<?php 
	$username="fuelconsumption";
	$password="fuelconsumption";
	$database="fuelconsumption";
	mysql_connect("mysql.altgilbers.com",$username,$password);
	mysql_select_db($database) or die( "Unable to select database");
?>