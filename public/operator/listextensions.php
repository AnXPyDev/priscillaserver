<?php

require_once "Endpoint.php";
require_once "Response.php";

new class extends Endpoint {
    function handle() {
        return new ResponseSuccess([
            "extensions" => get_loaded_extensions()
        ]);
    }
};