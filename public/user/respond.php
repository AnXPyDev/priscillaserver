<?php

require_once "UserEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends UserEndpoint {
    function handle_user() {
        global $database;
        $request_id = $this->data['request_id'] ?? null;
        $watch_code = $this->data['watch_code'] ?? null;
        $data = $this->data['data'] ?? null;

        if (any_null($request_id, $watch_code, $data)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_get_request = $db->prepare(
            'select `room`.`watch_code` from `request`' . 
            ' join `client` on `request`.`client_id`=`client`.`id`' . 
            ' join `room` on `client`.`room_id`=`room`.`id`' . 
            ' where `request`.`id`=:request_id'
        );

        $qry_get_request->execute([
            ':request_id' => $request_id
        ]);

        if ($qry_get_request->rowCount() == 0) {
            return new ResponseError("No such request");
        }


        $res = $qry_get_request->fetch(PDO::FETCH_NUM);
        $wc = $res[0];

        if ($wc != $watch_code) {
            return new ResponseError("Invalid watch code");
        }

        $qry_put_response = $db->prepare('insert into `response` (`request_id`, `data`) values (:request_id, :data)');
        $qry_put_response->execute([
            ':request_id' => $request_id,
            ':data' => json_encode($data)
        ]);

        $event = new SyncEvent("Request" . $request_id);
        $event->fire();
    }


};