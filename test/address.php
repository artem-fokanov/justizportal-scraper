<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SyntaxParser.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';

$db = new Database();
$query = $db->query('SELECT i.entity, a.plaintext FROM article a LEFT JOIN main.info i on a.id = i.article_id')->fetchAll($db::FETCH_ASSOC);
?>
<link rel="stylesheet" href="table.css"/>
<table>
    <thead>
    <tr>
        <th>Entity</th>
        <th>Entity_Address</th>
        <th>Plaintext</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($query as $item) :
//        $a = SyntaxParser::parseAddress($item['entity'], $item['plaintext']);?>
    <tr>
        <td><?=$item['entity']?></td>
        <td><?=SyntaxParser::parseAddress($item['entity'], $item['plaintext'])?></td>
        <td><?=$item['plaintext']?></td>
    </tr>
    <?php endforeach; ?>

    </tbody>
</table>