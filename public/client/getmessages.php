<?php

require_once "ClientEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends ClientEndpoint {
    function handle_client() {
        global $database;

        $last_id = $this->data['last_id'] ?? null;

        if (is_null($last_id)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_get_messages = $db->prepare('select * from `message` where `client_id`=:client_id and `id`>:last_id');

        $qry_get_messages->execute([
            ':client_id' => $this->client['id'],
            ':last_id' => $last_id
        ]);

        return new ResponseSuccess([
            'messages' => $qry_get_messages->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

};