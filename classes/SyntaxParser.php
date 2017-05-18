<?php

class SyntaxParser {

    /**
     * This method separates entity, id and court from the results page
     *
     * @param $str
     * @return array
     */
    public static function parseDataFromResultList($str) {
        preg_match('/(.*),\s(\s?\w?\s?\d{0,4}\w?\s?IN\s\d{0,4}\/\d{0,2}\s?[a-zA-Z0-9-() ]{0,5}),\sRegistergericht (.*)/', $str, $matches);
        $output = [
            'id' => $matches[2],
            'entity' => $matches[1],
            'court' => $matches[3],
        ];
        return $output;
    }

    /**
     * Get the total amount of articles from the results page
     *
     * @param $str
     * @return int
     */
    public static function parseResultSum($str) {
        $int = '0123456789';
        $pos = strpbrk($str, $int);
        $result = substr($pos, 0, strspn($pos, $int));
        return intval($result);
    }

    /**
     * Get the address from article's plaintext
     *
     * Search first(entity/enterprise) and last(region/city) words of entity to set "extract range" from the plaintext.
     * This will allow with 99% chance to find and parse full address from article
     *
     * @param $entity
     * @param $plaintext
     * @return bool|string
     */
    public static function parseAddress($entity, $plaintext) {
        $firstEntityWord = substr($entity, 0, strpos($entity, ' '));
        $lastEntityWord = trim(substr($entity, strrpos($entity, ',')+1, strlen($entity)));

        $addressStart = strpos($plaintext, $firstEntityWord);
        $addressStop = strpos($plaintext, $lastEntityWord, $addressStart) + strlen($lastEntityWord);

        $address = substr($plaintext, $addressStart, $addressStop-$addressStart);

        if ($addressStart > $addressStop) {
            $address = $entity; // if smth went wrong in parse
        } elseif (strlen($entity) > strlen($address)) {
            $addressStop2 = strpos($plaintext, $lastEntityWord, $addressStop) + strlen($lastEntityWord);
            $address = substr($plaintext, $addressStart, $addressStop2-$addressStart);
        }

        return $address;
    }

    /**
     * Get the court from article's plaintext
     *
     * The court info listed in results page almost enough to store in DB.
     * But some of them can be extended by (AG CourtName CourtNumber) pattern
     *
     * @param $court
     * @param $plaintext
     * @return bool|string
     */
    public static function parseCourt($court, $plaintext) {
        $search = '(AG '.$court.')';
        $courtStart = strpos($plaintext, $search);

        if ($courtStart !== false) {
            $courtStop = strlen($search)-2;
            $court = substr($plaintext, $courtStart+1, $courtStop);
        }

        return $court;
    }

    /**
     *
     * This method realises 2 scenarios to find lawyer and its contact_info
     * 1) Is to use regular expression that extracts everything between "Rechtsanwalt" and "Verfügungen" words due. This scenario covers at least 50% of total articles
     * 2) Is to use string checks for some of words (e.g. phone, fax)
     * 3) Search for a nominative and plural cases of the "Rechtsanwalt" word
     * @param $plaintext
     * @return null|string
     */
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

    /**
     * @param $plaintext
     * @return int
     */
    public static function checkTemproratity($plaintext) {
        return intval(stripos($plaintext, 'vorläufig') !== false);
    }
}