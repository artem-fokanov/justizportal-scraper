<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SyntaxParser.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';

$db = new Database();
$query = $db->query('SELECT * FROM article')->fetchAll($db::FETCH_ASSOC);
?>
<link rel="stylesheet" href="table.css"/>
<table>
    <thead>
    <tr>
        <th>Temporarity</th>
        <th>Plaintext</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($query as $item) :
        $a = SyntaxParser::checkTemproratity($item['plaintext']);?>
    <tr>
        <td><?=intval($a)?></td>
        <td><?=$item['plaintext']?></td>
    </tr>
    <?php endforeach; ?>

    </tbody>
</table>