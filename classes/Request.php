<?php

class Request {
    const REQUEST_POST = 0;
    const REQUEST_GET = 1;

    protected $phpsessid;

    protected $properties = [
        'Suchfunktion' => 'uneingeschr',
        'Absenden' => 'Suche starten',
        'Bundesland' => '-- Alle Bundesländer --',
        'Gericht' => '-- Alle Insolvenzgerichte --',
        'Datum1' => '',
        'Datum2' => '',
        'Name' => '',
        'Sitz' => '',
        'Abteilungsnr' => '',
        'Registerzeichen' => '--',
        'Lfdnr' => '',
        'Jahreszahl' => '--',
        'Registerart' => 'HRB',
        'select_registergericht' => '',
        'Registergericht' => '-- keine Angabe --',
        'Registernummer' => '',
        'Gegenstand' => 'Sicherungsmaßnahmen',
        'matchesperpage' => '100',
        'page' => '1',
        'sortedby' => 'Datum',
//        'PHPSESSID' => ''
    ];

    protected $data = [];

    public function sendListRequest($link, $params = [], $method = self::REQUEST_POST, $page=null) {
        $params = $this->processParams($params);
        $params = http_build_query($params);

        if ($method == self::REQUEST_GET) {
            $link = $link.'?'.$params;
        }

        $ch = curl_init($link);

        if ($method == self::REQUEST_POST) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // attach post data to request
        }
        curl_setopt($ch, CURLOPT_HEADER, 0); // add headers to get session id from cookie
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // output to file
//        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36"); // set Chrome user-agent

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function processParams($params = []) {
        $output = $this->properties;
        foreach ($params as $param => $value) {
            if (array_key_exists($param, $this->properties) || $param == 'PHPSESSID') {
                $output[$param] = $value;
            }
        }
        foreach ($output as &$item) {
            $item = iconv('UTF-8', 'ISO-8859-1', $item);
        }

        return $output;
    }
}