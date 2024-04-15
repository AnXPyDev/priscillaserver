<?php

require_once "Endpoint.php";
require_once "Database.php";
require_once "Auth.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        global $database;
        global $auth;

        $name = $this->data["name"] ?? null;
        $join_code = $this->data["joinCode"] ?? null;

        if (any_null($name, $join_code)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_get_room = $db->prepare('select * from `room` where `join_code`=:join_code');

        $qry_get_room->execute([
            ':join_code' => $join_code
        ]);

        if ($qry_get_room->rowCount() == 0) {
            return new ResponseError("Invalid join code");
        }

        $room = $qry_get_room->fetch(PDO::FETCH_ASSOC);

        $client_secret = $auth->make_random_string(12);

        $qry_insert_client = $db->prepare('insert into `client` (`name`, `secret`, `room_id`, `ip_adress`) values (:name, :secret, :room_id, :ip_adress)');
        $qry_insert_client->execute([
            ':name' => $name,
            ':secret' => $client_secret,
            ':room_id' => $room['id'],
            ':ip_adress' => $_SERVER['REMOTE_ADDR'] ?? 'unknown IP'
        ]);

        $qry_insert_event = $db->prepare('insert into `room_event` (`room_id`, `data`) values (:room_id, :data)');
        $qry_insert_event->execute([
            ':room_id' => $room['id'],
            ':data' => json_encode([
                'message' => "Client '" . $name . "' joined the room"
            ])
        ]);

        return new ResponseSuccess([
            'secret' => $client_secret,
            'roomName' => $room['name'],
            'clientConfiguration' => json_decode($room['config'], true)
        ]);
    }
};