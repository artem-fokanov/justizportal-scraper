<?php
require_once 'classes' . DIRECTORY_SEPARATOR . 'Request.php';
require_once 'classes' . DIRECTORY_SEPARATOR . 'Parser.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$startTime = microtime(true);

//REQUEST
$site = 'https://www.insolvenzbekanntmachungen.de';
$queryString = '/cgi-bin/bl_suche.pl';

$rq = new Request();
$page = 1;
$html = $rq->sendListRequest($site.$queryString);


//PARSE
$parser = new Parser($html);
try {
    $pages = $parser->totalPages();
    $totalLinks = $parser->totalLinks();
    $links = $parser->parseLinks();
    $sessionID = $parser->getSessionId();
} catch (Exception $e) {
}


try {
    while ($page < $pages) {
        $html = $rq->sendListRequest($site.$queryString, ['page' => ++$page.'#Ergebnis', 'PHPSESSID' => $sessionID], $rq::REQUEST_GET);

        $links = array_merge($links, $parser->html($html)->parseLinks());
    }

} catch (Exception $e) {

}
//unset($parser);

$endTime = microtime(true);

echo ($endTime - $startTime), ' seconds';

echo '<br/>';
print_r($links);