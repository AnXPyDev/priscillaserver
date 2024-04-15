<?php

require_once "ClientEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends ClientEndpoint {
    function handle_client() {
        global $database;

        $requests = $this->data['requests'] ?? null;

        if (is_null($requests)) {
            return new ResponseError("Bad input");
        }

        $db = $database->ensure();

        $results = [];

        $client_id = $this->client['id'];

        $qry_get_response = $db->prepare(
            'select `response`.`data` from `request` join `response` on `request`.`id`=`response`.`request_id` where `request`.`id`=:request_id and `request`.`client_id`=:client_id'
        );

        foreach ($requests as $request_id) {
            $qry_get_response->execute([
                ':request_id' => $request_id,
                ':client_id' => $client_id
            ]);

            if ($qry_get_response->rowCount() == 0) {
                continue;
            }

            $results['' . $request_id] = $qry_get_response->fetch()[0];
        }

        return new ResponseSuccess([
            'response' => $results
        ]);
    }
};