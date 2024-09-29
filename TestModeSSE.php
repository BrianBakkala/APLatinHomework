<?php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

 

require_once ('SQLConnection.php');  



$hint = "";

if(isset($_REQUEST['timestampupdate']))
{

	$hint =  json_encode(SQLQuarry('SELECT `id`, `TestModeAP`, `TestMode4`, `TestMode3` FROM `Control Panel`')); 
}

$time = date('r');
echo "data:  {$hint}\n\n";
flush();



?>