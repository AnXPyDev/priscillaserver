<?php

require_once "WatchEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends WatchEndpoint {
    function handle_room() {
        global $database;

        $db = $database->ensure();

        $room = $this->room;

        $response = [];

        $client_id = $this->data['client_id'] ?? null;

        $last_client_event_id = $this->data['last_client_event_id'] ?? null;
        if (is_null($last_client_event_id)) {
            goto skip_client_events;
        }

        $qry_get_client_events = $db->prepare('select * from `client_event` where `room_id`=:room_id' . (is_null($client_id) ? '' : ' and `client_id`=:client_id') . ' and `id` > :last_id');
        $qry_get_client_events->execute(is_null($client_id) ? [
            ':room_id' => $room['id'],
            ':last_id' => $last_client_event_id
        ] : [
            ':room_id' => $room['id'],
            ':client_id' => $client_id,
            ':last_id' => $last_client_event_id
        ]);

        $response['client_events'] = $qry_get_client_events->fetchAll(PDO::FETCH_ASSOC);


        skip_client_events:;


        $last_room_event_id = $this->data['last_room_event_id'] ?? null;
        if (is_null($last_room_event_id)) {
            goto skip_room_events;
        }
        
        $qry_get_room_events = $db->prepare('select * from `room_event` where `room_id`=:room_id' . (is_null($client_id) ? '' : ' and `client_id`=:client_id') . ' and `id` > :last_id');
        $qry_get_room_events->execute(is_null($client_id) ? [
            ':room_id' => $room['id'],
            ':last_id' => $last_room_event_id
        ] : [
            ':room_id' => $room['id'],
            ':client_id' => $client_id,
            ':last_id' => $last_room_event_id
        ]);

        $response['room_events'] = $qry_get_room_events->fetchAll(PDO::FETCH_ASSOC);
        
        skip_room_events:;

        $last_request_id = $this->data['last_request_id'] ?? null;
        if (is_null($last_request_id)) {
            goto skip_requests;
        }

        $qry_get_requests = $db->prepare(
            'select `request`.* from `request` join `client` on `request`.`client_id`=`client`.`id` left join `response` on `request`.`id`=`response`.`request_id`'
            . 'where `response`.`data` is null and `client`.`room_id`=:room_id' . ' and `request`.`id`>:last_id' . 
            (is_null($client_id) ? '' : ' and `client`.`id`=:client_id')
        );

        $qry_get_requests->execute(is_null($client_id) ? [
            ':room_id' => $room['id'],
            ':last_id' => $last_request_id
        ] : [
            ':room_id' => $room['id'],
            ':client_id' => $client_id,
            ':last_id' => $last_request_id
        ]);

        $response['requests'] = $qry_get_requests->fetchAll(PDO::FETCH_ASSOC);

        skip_requests:;

        return new ResponseSuccess($response);
    }
};