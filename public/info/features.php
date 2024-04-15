<?php

require_once "Endpoint.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        return new ResponseSuccess([
            'supervisor' => [
                'protocol' => 'http'
            ],
            'requests' => [
                'protocol' => 'http-refresh'
            ]
        ]);
    }
};