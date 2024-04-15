<?php

require_once "UserEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends UserEndpoint {
    function handle_user() {
        global $database;

        $client_id = $this->data['client_id'] ?? null;
        $data = $this->data['data'] ?? null;

        if (any_null($client_id, $data)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_insert_message = $db->prepare('insert into `message` (`client_id`, `data`) values (:client_id, :data)');
        $qry_insert_message->execute([
            ':client_id' => $client_id,
            ':data' => $data
        ]);
    }
};