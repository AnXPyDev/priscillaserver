<?php

require_once "UserEndpoint.php";
require_once "Response.php";
require_once "Database.php";
require_once "Auth.php";

new class extends UserEndpoint {
    function handle_user() {
        global $database;
        global $auth;

        $name = $this->data["name"] ?? $this->user['displayname'] . "'s room";
        $config = $this->data["config"];

        $db = $database->ensure();

        if (is_null($config)) {
            return new ResponseError("No config provided");
        }

        $join_code = $auth->make_random_string(6);
        $watch_code = $auth->make_random_string(12);
        $owner_id = $this->user["id"];

        $qry_insert_room = $db->prepare('insert into `room` (`name`, `join_code`, `watch_code`, `config`, `owner_id`) values (:name, :join_code, :watch_code, :config, :owner_id)');

        $qry_insert_room->execute([
            ':name' => $name,
            ':join_code' => $join_code,
            ':watch_code' => $watch_code,
            ':config' => $config,
            ':owner_id' => $owner_id
        ]);

        $room_id = $db->lastInsertId();

        $qry_insert_event = $db->prepare('insert into `room_event` (`room_id`, `data`) values (:room_id, :data)');

        $qry_insert_event->execute([
            ':room_id' => $room_id,
            ':data' => json_encode([
                'message' => "Room created"
            ])
        ]);

        return new ResponseSuccess([
            'id' => $room_id,
            'name' => $name,
            'join_code' => $join_code,
            'watch_code' => $watch_code
        ]);
    }
};