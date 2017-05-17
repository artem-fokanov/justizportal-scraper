<?php


//require __DIR__. DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$url = 'https://www.insolvenzbekanntmachungen.de/cgi-bin/bl_suche.pl';
$post = 'Suchfunktion=uneingeschr&Absenden=Suche+starten&Bundesland=--+Alle+Bundesl%E4nder+--&Gericht=--+Alle+Insolvenzgerichte+--&Datum1=&Datum2=&Name=&Sitz=&Abteilungsnr=&Registerzeichen=--&Lfdnr=&Jahreszahl=--&Registerart=HRB&select_registergericht=&Registergericht=--+keine+Angabe+--&Registernummer=&Gegenstand=Sicherungsma%DFnahmen&matchesperpage=100&page=1&sortedby=Datum';
$ch = curl_init($url);
$curlOutput = fopen('curlPost.html', 'w');
curl_setopt($ch, CURLOPT_FILE, $curlOutput);
curl_setopt($ch, CURLOPT_HEADER, 1); // add headers to get session id from cookie
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // output to file

curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // attach post data to request

$response = curl_exec($ch);
curl_close($ch);
fclose($curlOutput);