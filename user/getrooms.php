<?php

include "../autoload.php";

require_once "UserEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends UserEndpoint {
    function handle_user() {
        global $database;

        $db = $database->ensure();
        $qry_get_rooms = $db->prepare('select `id`, `name`, `join_code`, `watch_code` from `room` where `owner_id`=:owner_id');
        $qry_get_rooms->execute([
            ':owner_id' => $this->user['id']
        ]);

        return new ResponseSuccess([
            'rooms' => $qry_get_rooms->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }
};