<?php
// Get variable junk
$stocked = $_GET["stocked"];
$item_name = $_GET["item_name"];

// Mysql stuff
mysql_pconnect( 'localhost', 'food', 'FTQ7P3LwxshbmatH'  );
mysql_select_db('food');

mysql_query(
	'UPDATE item 
	SET 
		stocked='.$stocked.',
		date_modified=\''.date("Y-m-d H:i:s").'\'
		
	WHERE name=\''.$item_name.'\''
);

?>