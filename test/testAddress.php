<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SyntaxParser.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';

$db = new Database();
$query = $db->query('SELECT * FROM article')->fetchAll($db::FETCH_ASSOC);
?>
<table>
    <thead>
    <tr>
        <th>1</th>
        <th>2</th>
        <th>3</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($query as $item) :
        $a = SyntaxParser::parseAddress($item['entity'], $item['plaintext']);?>
    <tr>
        <td><?=$item['entity']?></td>
        <td><?=$a?></td>
        <td><?=$item['plaintext']?></td>
    </tr>
    <?php endforeach; ?>

    </tbody>
</table>
$c = 1;