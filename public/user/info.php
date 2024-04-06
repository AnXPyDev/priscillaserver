<?php

require_once "UserEndpoint.php";
require_once "Response.php";

new class extends UserEndpoint {
    function handle_user() {
        $user = $this->user;

        return new ResponseSuccess([
            'id' => $user['id'],
            'username' => $user['username'],
            'displayname' => $user['displayname']
        ]);
    }
};