<?php

class Request {
    const REQUEST_POST = 0;
    const REQUEST_GET = 1;

    const REQUEST_SITE = 'https://www.insolvenzbekanntmachungen.de';

    protected $phpsessid;

    protected $descriptor;

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
    ];

    protected $data = [];

    public function send($link, $params = [], $method = self::REQUEST_POST, $isRawParams = false) {
        $link = self::REQUEST_SITE . $link;

        if (!$isRawParams) {
            $params = $this->processParams($params);
            $params = http_build_query($params);
        }

        if ($method == self::REQUEST_GET) {
            $glue = (strpos($link, '?') === false) ? '?' : '&';
            $link = $link . $glue . $params;
        }

        $ch = $this->descriptor;
        curl_reset($ch);

        curl_setopt($ch, CURLOPT_URL, $link);

        if ($params && $method == self::REQUEST_POST) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params); // attach post data to request
        }
        curl_setopt($ch, CURLOPT_HEADER, 0); // add headers to get session id from cookie
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // output to file

        $response = curl_exec($ch);

        return $response;
    }

    public function open() {
        if (is_null($this->descriptor)){
            $this->descriptor = curl_init();
        }
        return $this->descriptor;
    }

    public function close() {
        curl_close($this->descriptor);
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