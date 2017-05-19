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

        if (strlen($entity) > strlen($address)) { // Search occurence of "last entity word" (region/city) once again to wide again
            $addressStop = strpos($plaintext, $lastEntityWord, $addressStop) + strlen($lastEntityWord);
            $address = substr($plaintext, $addressStart, $addressStop-$addressStart);
        }
        if ($addressStart > $addressStop) {
            $address = $entity; // if smth went wrong in parse
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
//        preg_match('/Rechtsanw\S*\s/', $plaintext, $m, PREG_OFFSET_CAPTURE);
        $splite = preg_split('/Rechtsanw\S*\s/', $plaintext, -1, PREG_SPLIT_OFFSET_CAPTURE);
        array_shift($splite);
        if (count($splite)) {
            $lawyer = null;
            foreach ($splite as $substr) {
                $lawyerOffset = $substr[1];

                $dr = strpos($plaintext, 'Dr.', $lawyerOffset);
                $prof = strpos($plaintext, 'Prof.', $lawyerOffset);
                $fax = strpos($plaintext, 'Fax', $lawyerOffset);
                $email = strpos($plaintext, 'Email', $lawyerOffset);
                $phone = strpos($plaintext, 'Telefon', $lawyerOffset);
                $telefax = strpos($plaintext, 'Telefax', $lawyerOffset);
                $contact = [
                    ($dr !== false) ? $dr + strlen('Dr.') : false,
                    ($prof !== false) ? $prof + strlen('Prof.') : false,
                    ($fax - $lawyerOffset < 300) ? $fax : false,
                    ($email - $lawyerOffset < 300) ? $email : false,
                    ($phone - $lawyerOffset < 300) ? $phone : false,
                    ($telefax - $lawyerOffset < 300) ? $telefax : false,
                ];
                $max = max($contact);
                if ($max == false) {
                    $max = $lawyerOffset;
                }
                $end = array_filter([
                    strpos($plaintext, 'bestellt', $max),
                    strpos($plaintext, 'Verfügungen', $max),
                    strpos($plaintext, 'wird gemäß', $max),
                    strpos($plaintext, 'auf Eröffnung', $max),
                    strpos($plaintext, 'Geschäftszweig', $max),
                    strpos($plaintext, 'Uhr', $max),
                ]);
                if (!empty($end) && (min($end) - $max < 100)) {
                    $end = min($end);
                } else {
                    $end = strpos($plaintext, '. ', $max);
                }
                $lawyer .= substr($plaintext, $lawyerOffset, $end - $lawyerOffset) . ' | ';
            }
            return $lawyer;
        }
        return null;
    }

    /**
     * @param $plaintext
     * @return int
     */
    public static function checkTemproratity($plaintext) {
        return intval(stripos($plaintext, 'vorläufig') !== false);
    }
}