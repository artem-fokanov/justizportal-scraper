<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 17.05.2017
 * Time: 15:44
 */
class SyntaxParser {

    public static function parseDataFromResultList($str) {
        preg_match('/(.*),\s(\s?\w?\s?\d{0,4}\w?\s?IN\s\d{0,4}\/\d{0,2}\s?[a-zA-Z0-9-() ]{0,5}),\sRegistergericht (.*)/', $str, $matches);
        return $matches;
    }

    public static function parseResultSum($str) {
        return substr($str, 0, strspn($str, "0123456789"));
    }
}