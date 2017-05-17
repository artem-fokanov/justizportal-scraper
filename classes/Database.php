<?php

class Database extends \PDO {

    public function __construct() {
        $file = dirname(dirname(__FILE__)).'/db/mydb.sq3';
        $dsn = 'sqlite:'.$file;
        parent::__construct($dsn);
        $this->createTables();
    }

    public function createTables() {
//        $this->query('CREATE TABLE IF NOT EXISTS links (id,v);');
    }
}