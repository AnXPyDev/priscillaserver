<?php

require_once "WatchEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends WatchEndpoint {
    function handle_room() {
        global $database;

        $db = $database->ensure();

        $qry_get_clients = $db->prepare('select * from `client` where `client`.`room_id`=:room_id');

        $qry_get_clients->execute([
            ':room_id' => $this->room['id']
        ]);

        return new ResponseSuccess([
            'clients' => $qry_get_clients->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }
};