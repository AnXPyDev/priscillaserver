<?php

require_once "Endpoint.php";
require_once "Auth.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        global $database;
        global $auth;

        $username = $this->data["username"] ?? null;
        $password = $this->data["password"] ?? null;
        $displayname = $this->data["displayname"] ?? $username;

        if (any_null($username, $password)) {
            return new ResponseError("Bad input");
        }


        if (!$auth->check_password($password)) {
            return new ResponseError("Bad password");
        }

        $salt = $auth->make_salt();
        $hash = $auth->make_hash($password, $salt);

        $db = $database->ensure();

        $qry_insert_user = $db->prepare(
            'insert into `user` (`username`, `displayname`, `password_salt`, `password_hash`) values (:username, :displayname, :salt, :hash)'
        );


        try {
            $qry_insert_user->execute([
                ':username' => $username,
                ':displayname' => $displayname,
                ':salt' => $salt,
                ':hash' => $hash
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return new ResponseError("User " . $username . " already exists");
            }
            throw $e;
        }
    }
};