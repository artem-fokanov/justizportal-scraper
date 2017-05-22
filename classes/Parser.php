<?php

// Load SimpleHTMLDom
require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
//require_once __DIR__ . DIRECTORY_SEPARATOR . 'SyntaxParser.php';

class Parser {

    /**
     * @var bool|\SimpleHtmlDom\simple_html_dom
     */
    protected $page;

    /**
     * @var \SimpleHtmlDom\simple_html_dom
     */
    protected $resultBlock;

    /**
     *
     * @var: strores PHPSESSID for further search
     */
    protected $sessionId;

    public function __construct($html) {
        $this->page = \SimpleHtmlDom\str_get_html($html);
    }

//    public function __destruct() {
//        $this->page->clear();
//    }

    /**
     * Build DOM tree from string
     *
     * @param $html
     * @return $this
     */
    public function html($html) {
        $this->clear();

        $this->page = \SimpleHtmlDom\str_get_html($html);

        return $this;
    }

    protected function clear() {
        $this->page->clear();
        $this->resultBlock = null;
    }

    /**
     * Return result Node with elements
     *
     * @return mixed
     */
    protected function result() {
        $form = $this->page->find('form[name=globe]', 0);

        if (is_null($this->resultBlock)) {
            $this->resultBlock = $form->nextSibling()->find('a[name=Ergebnis]', 0)->parent();
        }

        return $this->resultBlock;
    }

    /**
     * Total amount of article provided by search query
     *
     * @return mixed
     */
    public function totalLinks() {
        $resultBlock = $this->result();

        $totalLinks = $resultBlock->find('p[align=center]', 0)->plaintext;
        $totalLinks = SyntaxParser::parseResultSum($totalLinks);

        return $totalLinks;
    }

    /**
     * Total amount of pages
     *
     * @return int
     */
    public function totalPages() {
        $resultBlock = $this->result();

        $lastPage = 0;
        $lastPageButton = '>|';

        foreach ($resultBlock->find('center', 0)->find('a[href]') as $page) {
            if ($page->plaintext == $lastPageButton) {
                preg_match('/&page=(\d+)/', $page->attr['href'], $matches);

                $lastPage = array_key_exists(1, $matches) ? intval($matches[1]) : 1;
            }
        }

        return $lastPage;
    }

    /**
     * Parsing and extracting data from string.
     *
     * Output format stores in 2-level tree
     * links:
     *      link_href:
     *          ID
     *          Entity
     *          Lawyer
     *
     * @return array
     */
    public function parseLinks() {
        $links = [];
        foreach ($this->result()->find('li') as $item) {
            $text = SyntaxParser::parseDataFromResultList($item->children(0)->children(0)->plaintext); // separate entity & id & court from plaintext

            $date = trim($item->children(0)->nodes[0]->plaintext);
            $text['date'] = $date;

            $link = $item->children(0)->href;
            echo "Attaching link: ", $link, PHP_EOL;
            // extracting link;
            $link = str_replace('javascript:NeuFenster(\'', '', $link);
            $link = substr($link, 0, strlen($link)-2);
            // extract session id for further search results
            $link = $this->sessionIdFromLink($link);
            $text['session'] = $this->getSessionId();

            $links[$link] = $text;
        }
        return $links;
    }

    public function parseArticleAsText() {
        $str = html_entity_decode($this->page->find('body', 0)->plaintext);
        $str = str_replace(array("\r\n", "\n", "\r"), ' ', $str);
        return trim($str);
    }

    public function sessionIdFromLink($link) {
        preg_match('/PHPSESSID=(\w+)&/', $link, $matches);

        if (is_null($this->sessionId)) {
            $this->sessionId = array_key_exists(1, $matches) ? $matches[1] : null;
            echo "Session ID: ", $this->getSessionId(), PHP_EOL;
        }

        return str_replace($matches[0], '', $link);
    }

    public function getSessionId(){
        return $this->sessionId;
    }
}