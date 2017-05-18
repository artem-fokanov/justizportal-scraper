<?php

class Database extends \PDO {

    private $article = [
        'id',
        'entity_address',
        'court',
        'lawyer',
        'is_temporarily',
        'plaintext'
    ];

    private $links = [
        'id',
        'entity',
        'link'
    ];

    public function __construct() {
        $dirname = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'db';
        if (!is_dir($dirname))
            mkdir($dirname);

        $file = $dirname.'/mydb.sq3';
        $dsn = 'sqlite:'.$file;

        parent::__construct($dsn);

        $this->createTables();
    }

    public function createTables() {
        $this->query(<<<SQL
            CREATE TABLE IF NOT EXISTS article (
              --'id' text primary key,
              'id' text,
              'entity_address' text,
              'court' text,
              'lawyer' text,
              'is_temporarily' boolean,
              'plaintext' text
            );
SQL
        );
        $this->query(<<<SQL
            CREATE TABLE IF NOT EXISTS link (
              --'article_id' text primary key,
              'article_id' text,
              'entity' text,
              'link' text
            );
SQL
        );
    }

    public function insertArticle($data) {
        $statement = $this->prepare(<<<SQL
            INSERT INTO article
            (id, entity_address, court, lawyer, is_temporarily, plaintext)
            VALUES
            (:id, :entity_address, :court, :lawyer, :is_temporarily, :plaintext);
SQL
        );
        foreach ($data as $column => $value) {
            if (in_array($column, $this->article)) {
                $statement->bindValue(':'.$column, $value);
            }
        }
        $result = $statement->execute();

        return intval($result);
    }

    public function insertLink($data) {
        $statement = $this->prepare(<<<SQL
            INSERT INTO link
            (id, entity_address, court, lawyer, is_temporarily, plaintext)
            VALUES
            (:id, :entity_address, :court, :lawyer, :is_temporarily, :plaintext);
SQL
        );
        foreach ($data as $column => $value) {
            if (in_array($column, $this->article)) {
                $statement->bindValue(':'.$column, $value);
            }
        }
        $result = $statement->execute();

        return intval($result);
    }
}