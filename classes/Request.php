<?php

/**
 * Created by PhpStorm.
 * User: artem
 * Date: 17.05.2017
 * Time: 8:07
 */
class Request {

    protected $phpsessid;

    protected $properties = [
        'Suchfunktion' => '',
        'Absenden' => '',
        'Bundesland' => '--+Alle+Bundesl%E4nder+--',
        'Gericht' => '',
        'Datum1' => '',
        'Datum2' => '',
        'Name' => '',
        'Sitz' => '',
        'Abteilungsnr' => '',
        'Registerzeichen' => '',
        'Lfdnr' => '',
        'Jahreszahl' => '',
        'Registerart' => '',
        'select_registergericht' => '',
        'Registergericht' => '',
        'Registernummer' => '',
        'Gegenstand' => '',
        'matchesperpage' => '',
        'page' => '',
        'sortedby' => '',
        'PHPSESSID' => ''
    ];

    protected $data = [];

    public function __construct($link, $params = []) {
        $params = array_merge($this->properties, $params);
    }

    public function nextPage($page) {

    }

    public function openLink($href) {

    }
}