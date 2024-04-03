<?php

require_once "Endpoint.php";
require_once "Auth.php";
require_once "Database.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        global $database;
        global $auth;

        $data = $this->data;
        if (is_null($data) || is_null($data["session"])) {
            return new ResponseError("Bad input");
        }

        $user = $auth->get_user($data["session"]);

        if (is_null($user)) {
            return new ResponseError("Session invalid");
        }

        $db = $database->ensure();

        $qry_delete_sessions = $db->prepare('delete from `session` where `user_id`=:user_id');
        $qry_delete_sessions->execute([':user_id' => $user['id']]);
    }
};