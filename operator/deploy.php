<?php

include "../autoload.php";

require_once "Endpoint.php";
require_once "Database.php";

new class extends Endpoint {
    function handle() {
        global $config;
        global $database;
        $db = $database->ensure();
        $db->exec(file_get_contents("database/schema.sql"));
    }
};