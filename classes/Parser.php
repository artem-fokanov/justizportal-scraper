<?php

// Load SimpleHTMLDom
require_once dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'SyntaxParser.php';

class Parser {

    /**
     * @var bool|\SimpleHtmlDom\simple_html_dom
     */
    protected $page;

    /**
     * @var \SimpleHtmlDom\simple_html_dom
     */
    protected $resultBlock;

    protected $sessionId;

    public function __construct($html) {
        $this->page = \SimpleHtmlDom\str_get_html($html);
    }

    public function __destruct() {
        $this->page->clear();
    }

    public function html($html) {
        $this->page->clear();
        $this->resultBlock = null;

        $this->page = \SimpleHtmlDom\str_get_html($html);

        return $this;
    }

    /**
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
     * @return mixed
     */
    public function totalLinks() {
        $resultBlock = $this->result();

        $totalLinks = SyntaxParser::parseResultSum($resultBlock->find('p[align=center]', 0)->plaintext);

        return $totalLinks;
    }

    /**
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

    public function parseLinks() {
        $links = [];
        foreach ($this->result()->find('li') as $item) {
            $text = SyntaxParser::parseDataFromResultList($item->children(0)->children(0)->plaintext); // separate entity & id & court from plaintext

            $link = $item->children(0)->attr['href'];

            $this->sessionIdFromLink($link); // get  session id for further search results

            // extracting link;
            $extractHref = str_replace('javascript:NeuFenster(\'', '', $link);
            $links[] = substr($extractHref, 0, strlen($extractHref)-2);
        }
        return $links;
    }

    public function sessionIdFromLink($link) {
        if (is_null($this->sessionId)) {
            preg_match('/PHPSESSID=(\w+)/', $link, $matches);
            $this->sessionId = array_key_exists(1, $matches) ? $matches[1] : null;
        }
    }

    public function getSessionId(){
        return $this->sessionId;
    }
}