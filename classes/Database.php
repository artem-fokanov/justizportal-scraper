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
        'article_id',
        'article_date',
        'entity',
        'link',
    ];

    private $csv;

    public function __construct() {
        $dirname = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'db';
        if (!is_dir($dirname))
            mkdir($dirname);

        $file = $dirname.'/data.sq3';
        $dsn = 'sqlite:'.$file;

        parent::__construct($dsn);

        $this->createTables();
    }

    public function __destruct()
    {
        if (is_resource($this->csv))
            fclose($this->csv);
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
              'link' text,
              PRIMARY KEY (article_id, link) 
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

    public function existsInfo($data) {
        $statement = $this->prepare(<<<SQL
            SELECT COUNT(*) FROM info WHERE
            article_id = :article_id AND link = :link;
SQL
        );
        foreach ($data as $column => $value) {
            if ($column == 'id')
                $column = 'article_' . $column;

            if (in_array($column, ['article_id', 'link'])) {
                $statement->bindValue(':'.$column, $value);
            }
        }
        $statement->execute();
        $result = $statement->fetchColumn();
        return intval($result);
    }

    public function writeToCsv($data) {
        if (is_null($this->csv)) {
            $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . 'data.csv';

            if (!file_exists($file)) {
                $header = ['id', 'entity_address', 'court', 'lawyer','is_temporarily', 'plaintext'];
            }

            $this->csv = fopen($file, 'a');

            // Write headers to file if it wasn't already exist
            if (isset($header)) {
                fputcsv($this->csv, $header);
            }
        }

        fputcsv($this->csv, $data);
    }
}