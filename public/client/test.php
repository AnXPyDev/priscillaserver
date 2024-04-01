<?php
require_once "Endpoint.php";
require_once "Database.php";

new class extends Endpoint {
    function handle() {
        return new ResponseSuccess(array("message"=>"test"));
    }
};