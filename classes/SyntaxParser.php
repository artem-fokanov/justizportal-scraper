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
        $output = [
            'id' => $matches[2],
            'entity' => $matches[1],
            'court' => $matches[3],
        ];
        return $output;
    }

    public static function parseResultSum($str) {
        $int = '0123456789';
        $pos = strpbrk($str, $int);
        $result = substr($pos, 0, strspn($pos, $int));
        return intval($result);
    }

    public static function parseAddress($entity, $plaintext) {
        $firstEntityWord = substr($entity, 0, strpos($entity, ' '));
        $lastEntityWord = trim(substr($entity, strrpos($entity, ',')+1, strlen($entity)));

        $addressStart = strpos($plaintext, $firstEntityWord);
        $addressStop = strpos($plaintext, $lastEntityWord, $addressStart) + strlen($lastEntityWord);

        $address = substr($plaintext, $addressStart, $addressStop-$addressStart);

        return $address;
    }

    public static function checkTemproratity($plaintext) {
        return stripos($plaintext, 'vorläufig') !== false;
    }
}