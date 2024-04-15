<?php

require_once "ClientEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends ClientEndpoint {
    function handle_client() {
        global $database;

        $state = $this->data['state'] ?? null;

        if (is_null($state)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();
        
        $qry_update_state = $db->prepare('update `client` set `state`=:state where `id`=:id');

        $qry_update_state->execute([
            ':id' => $this->client['id'],
            ':state' => $state
        ]);
    }
};