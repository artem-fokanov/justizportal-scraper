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

//STORE KEYS
$fp = fopen('db/data.csv', 'w');

fputcsv($fp, ['id', 'entity_address', 'court', 'lawyer','is_temporarily', 'plaintext']);
try {
    $db = new Database();
    $db->beginTransaction();
    foreach ($links as $link => $data) {
        $articleHtml = $rq->send($site.$link, null);

        $text = trim($parser->html($articleHtml)->parseArticleAsText());

        $address = SyntaxParser::parseAddress($data['entity'], $text);

        $court = SyntaxParser::parseCourt($data['court'], $text);

        $lawyer = SyntaxParser::parseLawyer($text);

        $temporarity = SyntaxParser::checkTemproratity($text);

        $data = array_merge($data, [
            'plaintext' => $text,
            'entity_address' => $address,
            'court' => $court,
            'lawyer' => $lawyer,
            'is_temporarily' => $temporarity,
        ]);

        fputcsv($fp, [$data['id'], $data['entity_address'], $data['court'], $data['lawyer'], $data['is_temporarily'], $data['plaintext']]);
        echo "-pasted CSV- ";
        $db->insertArticle($data);
        echo "-pasted DB- ";
        $db->insertLink(array_merge($data, ['link' => $link]));
        echo "-ID \"{$data['id']}\"";
        echo (array_key_exists('DOCUMENT_ROOT', $_SERVER)) ? nl2br(PHP_EOL) : PHP_EOL;
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
}

//$rq->close();
fclose($fp);

$endTime = microtime(true);

echo ($endTime - $startTime), ' seconds';