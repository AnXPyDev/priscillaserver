<?php

require_once "Database.php";
require_once "Response.php";

$auth = new class {
    function make_salt() {
        return $this->make_random_string(32);
    }

    function make_random_string(int $length) {
        return substr(base64_encode(random_bytes($length * 2)), 0, $length);
    }

    function make_hash(string $password, string $salt) {
        return hash("sha256", $password . $salt);
    }

    function make_session() {
        return $this->make_random_string(32);
    }

    function check_password(string $password) {
        return strlen($password) > 6;
    }

    function get_user(string $session) {
        global $database;
        
        $db = $database->ensure();

        $qry_get_session = $db->prepare('select * from `session` where `id`=:session');
        $qry_get_session->execute([
            ':session' => $session
        ]);

        if ($qry_get_session->rowCount() == 0) {
            return null;
        }

        $user_id = $qry_get_session->fetch(PDO::FETCH_ASSOC)['user_id'];

        $qry_get_user = $db->prepare('select * from `user` where `id`=:user_id');
        $qry_get_user->execute([':user_id' => $user_id]);

        $user = $qry_get_user->fetch(PDO::FETCH_ASSOC);

        return $user;
    }
};