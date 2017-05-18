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

    private $info = [
        'artice_id',
        'article_date',
        'entity',
        'link',
    ];

    public function __construct() {
        $dirname = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'db';
        if (!is_dir($dirname))
            mkdir($dirname);

        $file = $dirname.'/data.sq3';
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
            CREATE TABLE IF NOT EXISTS info (
              --'article_id' text primary key,
              'article_id' text,
              'article_date' text,
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

    public function insertInfo($data) {
        $statement = $this->prepare(<<<SQL
            INSERT INTO info
            (article_id, article_date, entity, link)
            VALUES
            (:article_id, :article_date, :entity, :link);
SQL
        );
        foreach ($data as $column => $value) {
            if ($column == 'id' || $column == 'date')
                $column = 'article_' . $column;

            if (in_array($column, $this->info)) {
                $statement->bindValue(':'.$column, $value);
            }
        }
        $result = $statement->execute();

        return intval($result);
    }
}