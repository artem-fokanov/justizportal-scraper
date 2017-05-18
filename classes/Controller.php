<?php

class Controller {

    public function parser() {
        $startTime = microtime(true);

        $registerant = (array_key_exists('Registerart', $_GET)) ? $_GET['Registerart'] : null;

        //REQUEST
        $queryString = '/cgi-bin/bl_suche.pl';
        $rq = new Request();
        $page = 1;
        $html = $rq->send($queryString,
            [
                'Registerart' => $registerant
            ]
        );

        //PARSE
        $parser = new Parser($html);
        try {
            $pages = $parser->totalPages();
            echo "Total links detected: ", $parser->totalLinks(), PHP_EOL;
            $links = $parser->parseLinks();
            $sessionID = $parser->getSessionId();

            while ($page < $pages) {
                $html = $rq->send($queryString,
                    [
                        'Registerart' => $registerant,
                        'page' => ++$page . '#Ergebnis',
                        'PHPSESSID' => $sessionID
                    ],
                    $rq::REQUEST_GET);

                $links = array_merge($links, $parser->html($html)->parseLinks());
            }

        } catch (Exception $e) {

        }
        unset ($html, $pages, $page, $queryString);

        //STORE DATA
        $fp = fopen('db/data.csv', 'w');
        fputcsv($fp, ['id', 'entity_address', 'court', 'lawyer','is_temporarily', 'plaintext']);
        try {
            $db = new Database();

            $iteration = 1;
            foreach ($links as $link => $data) {
                $articleHtml = $rq->send($link,
                    http_build_query(['PHPSESSID' => $sessionID]),
                    $rq::REQUEST_GET,
                true
                );

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

                $db->beginTransaction();
                $inserted = $db->insertInfo(array_merge($data, ['link' => $link]));

                if ($inserted) {
                    $iteration++;
                    echo sprintf("%'.04d ", $iteration);
                    echo "-inserted data to Info table- ";

                    $db->insertArticle($data);
                    echo "-pasted data to Article table- ";
                    $db->commit();

                    fputcsv($fp, [$data['id'], $data['entity_address'], $data['court'], $data['lawyer'], $data['is_temporarily'], $data['plaintext']]);
                    echo "-pasted data in .csv- ";

                    echo "-ID \"{$data['id']}\"";
                    echo PHP_EOL;
                }
            }

        }
        catch (Exception $e) {
            $db->rollBack();
        }

        fclose($fp);

        $endTime = microtime(true);

        echo ($endTime - $startTime), ' seconds';
    }

    public function dashboard() {
        $title = 'Overview';
        $columns = ['id', 'entity_address', 'court', 'lawyer', 'is_temporarily', 'plaintext'];
        $query = '*';

        $db = new Database();

        if (array_key_exists('param', $_GET)) {
            switch ($_GET['param']) {
                case 'lawyer':
                    $title = 'Lawyers';
                    $query = 'id, lawyer, plaintext';
                    break;
                case 'entity_address':
                    $title = 'Entity & Addresses';
                    $query = 'id, entity_address, plaintext';
                    break;
                case 'court':
                    $title = 'Court';
                    $query = 'id, court, plaintext';
                    break;
                case 'is_temporarily':
                    $title = 'Court';
                    $query = 'id, is_temporarily, plaintext';
                    break;
                default:
                    break;
            }

            $columns = explode(', ', $query);
            array_walk($columns, function (&$value) {
                $value = ucfirst($value);
            });
        }

        return [
            'title' => $title,
            'columns' => $columns,
            'rows' => $db->query('SELECT '.$query.' FROM article')->fetchAll($db::FETCH_ASSOC)
        ];
    }
}