<?php


//require __DIR__. DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$url = 'https://www.insolvenzbekanntmachungen.de/cgi-bin/bl_suche.pl';

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HEADER, 1); // add headers to get session id from cookie
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // output to file

$response = curl_exec($ch);
curl_close($ch);