<?php
require_once 'classes' . DIRECTORY_SEPARATOR . 'Request.php';
require_once 'classes' . DIRECTORY_SEPARATOR . 'Parser.php';
require_once 'classes' . DIRECTORY_SEPARATOR . 'Database.php';

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

    while ($page < $pages) {
        $html = $rq->sendListRequest($site . $queryString, ['page' => ++$page . '#Ergebnis', 'PHPSESSID' => $sessionID], $rq::REQUEST_GET);

        $links = array_merge($links, $parser->html($html)->parseLinks());
    }

} catch (Exception $e) {

}

unset($parser, $rq);

//STORE KEYS
try {
    $db = new Database();
    $db->beginTransaction();
    foreach ($links as $link => $data) {
        $db->exec("INSERT INTO article(id, entity, court) VALUES(\"{$data[1]}\", \"{$data[0]}\", \"{$data[2]}\");");
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
}

//SAVE CSV
$fp = fopen('file.csv', 'w');

fputcsv($fp, ['ID', 'ENTITY', 'COURT']);
foreach ($links as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

$endTime = microtime(true);

echo ($endTime - $startTime), ' seconds';