<?php

if (utyGetGet('json', false)) {
	$json = utyGetGet('json', false);
	
	$user = '';
	$pwd = '';
	if (utyGetGet('user', false)) $user = utyGetGet('user', false);
	if (utyGetGet('pwd', false)) $user = utyGetGet('pwd', false);
	
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

