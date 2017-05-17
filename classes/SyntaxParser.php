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
        $int = '0123456789';
        $pos = strpbrk($str, $int);
        $result = substr($pos, 0, strspn($pos, $int));
        return intval($result);
    }

    public static function parseAddress($entity, $str) {
        $firstEntityWord = substr($entity, 0, strpos($entity, ' '));
        $lastEntityWord = trim(substr($entity, strrpos($entity, ',')+1, strlen($entity)));

        $addressStart = strpos($str, $firstEntityWord);
        $addressStop = strpos($str, $lastEntityWord, $addressStart) + strlen($lastEntityWord);

        $address = substr($str, $addressStart, $addressStop-$addressStart);

        return $address;
    }
}