<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SyntaxParser.php';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';

$db = new Database();
$query = $db->query('SELECT plaintext FROM article')->fetchAll($db::FETCH_ASSOC);
?>
<table>
    <link rel="stylesheet" href="table.css"/>
    <thead>
    <tr>
        <th>Lawyer</th>
        <th>Plaintext</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($query as $item) : ?>
    <tr>
        <td><?=SyntaxParser::parseLawyer($item['plaintext'])?></td>
        <td><?=$item['plaintext']?></td>
    </tr>
    <?php endforeach; ?>

    </tbody>
</table>