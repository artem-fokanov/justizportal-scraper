<?php

class Controller {

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