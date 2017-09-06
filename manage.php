<?php
// Get variable junk
$stocked = $_GET["stocked"];
$item_name = $_GET["item_name"];

// Mysql stuff
$url = parse_url(getenv("mysqli"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);

// $conn = new mysqli($server, $username, $password, $db);

mysql_pconnect($server, $username, $password);
mysql_select_db($db);

mysql_query(
	'UPDATE item 
	SET 
		stocked='.$stocked.',
		date_modified=\''.date("Y-m-d H:i:s").'\'
		
	WHERE name=\''.$item_name.'\''
);

?>