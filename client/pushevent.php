<?php

include "../autoload.php";

require_once "ClientEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends ClientEndpoint {
    function handle_client() {
        global $database;

        $data = $this->data["data"] ?? null;

        if (is_null($data)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_insert_event = $db->prepare('insert into `client_event` (`client_id`, `room_id`, `data`) values (:client_id, :room_id, :data)');

        $qry_insert_event->execute([
            ':client_id' => $this->client['id'],
            ':room_id' => $this->client['room_id'],
            ':data' => $data
        ]);
    }
};