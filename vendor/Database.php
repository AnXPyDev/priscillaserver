<?php

$db = new class {
    public $con;

    function __construct() {
        global $config;
        $this->con = new PDO('mysql:host='. $config['DATABASE_HOST'] . ';' . 'dbname=' . $config['DATABASE_NAME'], $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
    }
};