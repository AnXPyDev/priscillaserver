<?php

$database = new class {
    private $con;

    function __construct() {}

    function connect() {
        global $config;
        $this->con = new PDO('mysql:host='. $config['DATABASE_HOST'] . ';' . 'dbname=' . $config['DATABASE_NAME'], $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
    }

    function ensure() {
        if (is_null($this->con)) {
            $this->connect();
        }
        return $this->con;
    }
};