<?php

require_once "ClientEndpoint.php";
require_once "Database.php";
require_once "Response.php";

new class extends ClientEndpoint {
	function handle_client() {
		global $database;

		$state = $this->data['state'] ?? null;

		$db = $database->ensure();

		if (!is_null($state)) {
			$qry_update_state = $db->prepare('update `client` set `state`=:state where `id`=:id');

			$qry_update_state->execute([
				':id' => $this->client['id'],
				':state' => $state
			]);
		}

		$events = $this->data['events'] ?? null;

		if (!is_null($events)) {
			foreach ($events as $data) {
				$qry_insert_event = $db->prepare('insert into `client_event` (`client_id`, `room_id`, `data`) values (:client_id, :room_id, :data)');

				$qry_insert_event->execute([
					':client_id' => $this->client['id'],
					':room_id' => $this->client['room_id'],
					':data' => $data
				]);
			}
		}

	}
};
