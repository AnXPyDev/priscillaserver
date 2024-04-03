<?php

require_once "Endpoint.php";
require_once "Auth.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        global $database;
        global $auth;

        $data = $this->data;
        if (is_null($data) || is_null($data["username"]) || is_null($data["password"])) {
            return new ResponseError("Bad input");
        }

        $username = $data["username"];
        $password = $data["password"];
        $displayname = $data["displayname"] ?? $username;

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