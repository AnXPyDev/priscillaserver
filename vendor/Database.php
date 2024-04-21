<?php

$database = new class {
    private $con;

    function __construct() {}

    function connect() {
        global $config;
        $this->con = new PDO(
            $config['DATABASE_PROTO'] . 
            ':host='. $config['DATABASE_HOST'] . 
            ';port=' . $config['DATABASE_PORT'] .
            ';dbname=' . $config['DATABASE_NAME'],
            $config['DATABASE_USER'],
            $config['DATABASE_PASSWORD']
        );
    }

    function ensure() {
        if (is_null($this->con)) {
            $this->connect();
        }
        return $this->con;
    }
};