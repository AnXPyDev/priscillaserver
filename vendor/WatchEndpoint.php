<?php

require_once "UserEndpoint.php";
require_once "Database.php";
require_once "Response.php";

abstract class WatchEndpoint extends UserEndpoint {
    protected $room;

    abstract protected function handle_room();

    function handle_user() {
        global $database;

        $watch_code = $this->data['watch_code'] ?? null;

        if (is_null($watch_code)) {
            return new ResponseError("No watch code");
        }

        $db = $database->ensure();

        $qry_get_room = $db->prepare('select * from `room` where `watch_code`=:watch_code');
        $qry_get_room->execute([
            ':watch_code' => $watch_code
        ]);

        if ($qry_get_room->rowCount() == 0) {
            return new ResponseError("Invalid watch code");
        }

        $this->room = $qry_get_room->fetch();

        return $this->handle_room();
    }

}