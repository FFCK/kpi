<?php

if (isset($_GET['json']))
{
	$json = $_GET['json'];
	
	$user = '';
	$pwd = '';
	if (isset($_GET['user'])) $user = $_GET['user'];
	if (isset($_GET['pwd'])) $pwd = $_GET['pwd'];
	
//	echo 'json in write_evenement = '.$json;
	
	$handle = fopen('https://kayak-polo.info/connector/set_evenement.php?user='.$user.'&pwd='.$pwd.'&json='.urlencode($json), "rb");
	if($handle)
	{
		$contents = stream_get_contents($handle);
		fclose($handle);
		echo($contents);
	}
	else
	{
		echo '{ reading_error}';
	}
}
else
{
	echo '{ error }';
}

?>