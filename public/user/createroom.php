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
        $config_name = $this->data["config"] ?? null;
        $config_override = $this->data["config_override"] ?? null;

        $db = $database->ensure();

        $config = [];

        if (is_null($config_name)) {
            goto skip_qry;
        }

        $qry_get_config = $db->prepare('select `data` from `config` where `name`=:name');
        $qry_get_config->execute([':name' => $config_name]);

        if ($qry_get_config->rowCount() == 0) {
            return new ResponseError("No such config '$config_name'");
        }

        $row = $qry_get_config->fetch(PDO::FETCH_ASSOC);


        $config_default = json_decode($row['data'], true);

        error_log(var_export($config_default, true));


        $config = array_replace_recursive($config, $config_default);

        skip_qry:;

        if (is_null($config_override)) {
            goto skip_override;
        }

        $config = array_replace_recursive($config, $config_override);

        skip_override:;

        $join_code = $auth->make_random_string(6);
        $watch_code = $auth->make_random_string(12);
        $config_json = json_encode($config);
        $owner_id = $this->user["id"];

        $qry_insert_room = $db->prepare('insert into `room` (`name`, `join_code`, `watch_code`, `config`, `owner_id`) values (:name, :join_code, :watch_code, :config, :owner_id)');

        $qry_insert_room->execute([
            ':name' => $name,
            ':join_code' => $join_code,
            ':watch_code' => $watch_code,
            ':config' => $config_json,
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