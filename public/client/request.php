<?php

require_once "ClientEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends ClientEndpoint {
    function handle_client() {
        global $database;

        $timeout = $this->data['timeout'] ?? -1;
        $data = $this->data['data'] ?? null;
        if (is_null($data)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $qry_insert_event = $db->prepare('insert into `request` (`client_id`, `data`) value (:client_id, :data)');

        $qry_insert_event->execute([
            ':client_id' => $this->client['id'],
            ':data' => json_encode($data)
        ]);

        $request_id = (int)$db->lastInsertId();

        return new ResponseSuccess([
            'request_id' => $request_id
        ]);

        /*
        if (!$success) {
            return new ResponseError("Timed out");
        }

        $qry_get_response('select * from `response` where `request_id`=:request_id');
        $qry_get_response->execute([
            ':request_id' => $request_id
        ]);

        if ($qry_get_response->rowCount() == 0) {
            return new ResponseError("No response");
        }

        $response = $qry_get_response->fetch(PDO::FETCH_ASSOC);

        return new ReponseSuccess($response);
        */
        
    }
};