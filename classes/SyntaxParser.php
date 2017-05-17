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

    public static function parseCourt($court, $plaintext) {
        $search = '(AG '.$court.')';
        $courtStart = strpos($plaintext, $search);

        if ($courtStart !== false) {
            $courtStop = strlen($search)-2;
            $court = substr($plaintext, $courtStart+1, $courtStop);
        }

        return $court;
    }

    public static function parseLawyer($plaintext) {
        $lawyer = null;

        preg_match('/Insolvenzverwalter.*Rechtsanwalt(.*)Verfügungen/U', $plaintext, $matches);
        if (array_key_exists(1, $matches))
            return trim($matches[1]);

        $searchTip1 = 'Insolvenzverwalter';
        $liquidatorOffset = stripos($plaintext, $searchTip1);

        if ($liquidatorOffset !== false) {

            $searchTip2 = 'Rechtsanwalt';
            $lawyerStartOffset = strpos($plaintext, $searchTip2, $liquidatorOffset);

            if ($lawyerStartOffset !== false) {
                $lawyerStartOffset += strlen($searchTip2);
                $lawyerStopOffset = strpos($plaintext, '. ', $lawyerStartOffset + 10);
            } else {
                $lawyerStartOffset = $liquidatorOffset + strlen($searchTip1);
            }

            $searchTip3 = 'Verfügungen';
            $injunctionsOffset = strpos($plaintext, $searchTip3, $liquidatorOffset);

            if ($injunctionsOffset !== false) {
                $lawyerStopOffset = $injunctionsOffset;
            }

            if (isset($lawyerStartOffset, $lawyerStopOffset) && $lawyerStartOffset !== false && $lawyerStopOffset !== false)
                $lawyer = trim(substr($plaintext, $lawyerStartOffset, $lawyerStopOffset-$lawyerStartOffset));
        }

        return $lawyer;
    }

    public static function checkTemproratity($plaintext) {
        return intval(stripos($plaintext, 'vorläufig') !== false);
    }
}