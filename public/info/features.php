<?php

require_once "Endpoint.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        return new ResponseSuccess([
            'supervisor' => [
                'http' => true,
                'sockets' => false
            ],
            'watcher' => [ 
                'http' => true,
                'sockets' => false
            ]
        ]);
    }
};