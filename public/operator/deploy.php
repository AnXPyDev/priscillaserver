<?php

require_once "Endpoint.php";

new class extends Endpoint {
    function handle() {
        global $config;
        $con = new PDO($config['DATABASE_PROTO'] . ":hostname=" . $config['DATABASE_HOST'], $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
        $con->exec("CREATE DATABASE IF NOT EXISTS`" . $config['DATABASE_NAME'] . "`;");
        $con->exec("USE `" . $config['DATABASE_NAME'] . "`;");
        $con->exec(file_get_contents("database/schema.sql"));
    }
};