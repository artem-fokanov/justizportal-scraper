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
$html = $rq->send($site.$queryString);


//PARSE
$parser = new Parser($html);
try {
    $pages = $parser->totalPages();
    $totalLinks = $parser->totalLinks();
    $links = $parser->parseLinks();
    $sessionID = $parser->getSessionId();

    while ($page < $pages) {
        $html = $rq->send($site . $queryString, ['page' => ++$page . '#Ergebnis', 'PHPSESSID' => $sessionID], $rq::REQUEST_GET);

        $links = array_merge($links, $parser->html($html)->parseLinks());
    }

} catch (Exception $e) {

}
unset ($html);
//unset($parser, $rq);

//STORE KEYS
$fp = fopen('db/data.csv', 'w');

fputcsv($fp, ['ID', 'ENTITY', 'COURT', 'PLAINTEXT']);
try {
    $db = new Database();
    $db->beginTransaction();
    foreach ($links as $link => $data) {
        $articleHtml = $rq->send($site.$link, null);

        $text = trim($parser->html($articleHtml)->parseArticleAsText());
        $data = array_merge($data, ['plaintext' => $text]);

        $address = SyntaxParser::parseAddress($data['entity'], $data['entity']);

        fputcsv($fp, $data);

        $db->exec("INSERT INTO article('id', 'entity', 'court', 'plaintext') VALUES('{$data['id']}', '{$data['entity']}', '{$data['court']}', '{$data['entity']}');");
//        $db->exec("INSERT INTO link('article_id', 'link') VALUES ('{$data[1]}', '$link');");
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
}

//$rq->close();
fclose($fp);

$endTime = microtime(true);

echo ($endTime - $startTime), ' seconds';