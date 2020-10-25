<?php

$data = utyGetGet('json_data');
$data = urldecode($data);

$fileName = "test.txt";
$fp = fopen("./cache/$fileName", 'w');
if ($fp)
{
	fwrite($fp, $data);
	fclose($fp);
	echo "1";
	return;
}

echo "0";
