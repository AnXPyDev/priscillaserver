<?php

include "../autoload.php";

require_once "UserEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends UserEndpoint {
    function handle_user() {
        global $database;

        $user = $this->user;

        $db = $database->ensure();

        $qry_delete_sessions = $db->prepare('delete from `session` where `user_id`=:user_id');
        $qry_delete_sessions->execute([':user_id' => $user['id']]);
    }
};