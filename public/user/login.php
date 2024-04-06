<?php

require_once "Endpoint.php";
require_once "Auth.php";
require_once "Database.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        global $database;
        global $auth;

        $username = $this->data["username"] ?? null;
        $password = $this->data["password"] ?? null;

        if (any_null($username, $password)) {
            return new ResponseError("Bad input");
        }


        $db = $database->ensure();

        $qry_get_user = $db->prepare('select * from `user` where `username`=:username');
        $qry_get_user->execute([':username' => $username]);

        if ($qry_get_user->rowCount() == 0) {
            return new ResponseError("User ". $username . " does not exist");
        }

        $user_data = $qry_get_user->fetch(PDO::FETCH_ASSOC);

        $hash = $auth->make_hash($password, $user_data['password_salt']);
        if ($hash != $user_data['password_hash']) {
            return new ResponseError("Wrong password");
        }

        $session = $auth->make_session();

        $qry_insert_session = $db->prepare('insert into `session` (`id`, `user_id`) values (:id, :user)');
        
        $qry_insert_session->execute([
            ':id' => $session,
            ':user' => $user_data['id']
        ]);

        return new ResponseSuccess(['session' => $session]);
    }
};