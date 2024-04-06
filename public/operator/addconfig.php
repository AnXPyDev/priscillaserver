<?php

require_once "Endpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        global $database;

        $name = $this->data["name"] ?? null;
        $data = $this->data["data"] ?? null;

        if (any_null($name, $data)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_insert_config = $db->prepare('insert into `config` (`name`, `data`) values (:name, :data)');

        $qry_insert_config->execute([
            ':name' => $name,
            ':data' => json_encode($data)
        ]);
    }
};