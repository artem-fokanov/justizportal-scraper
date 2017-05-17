<?php

class Database extends \PDO {

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
        $this->query('CREATE TABLE IF NOT EXISTS article (id,entity-address,court,lawyer,is_temporarily,plaintext);');
    }
}