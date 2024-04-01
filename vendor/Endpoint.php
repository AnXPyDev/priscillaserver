<?php

require_once "Response.php";

abstract class Endpoint {
    function __construct() {
        $this->internal_handle();    
    }
    
    abstract protected function handle();

    private function internal_handle() {
        $response = null;
        try {
            $response = $this->handle();
        } catch (Response $res) {
            $response = $res;
        } catch (Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $response = new ResponseError("Internal server error", 999);
        }

        if (is_null($response)) {
            $response = new ResponseSuccess();
        }
        
        header("Content-Type: application/json");
        echo json_encode(array_merge(
            array("code" => $response->getCode()),
            $response->getData()
        ));
    }
}