<?php

$data = "hello wordl !!!";
$data = urlencode($data);

$fp = fopen("http://www.ffck.org/classements_resultats/live/cache_transfert.php?json_data=".$data, 'r');
if ($fp)
{
	$result = fread($fp, 80);
	echo $result;
	fclose($fp);
}
