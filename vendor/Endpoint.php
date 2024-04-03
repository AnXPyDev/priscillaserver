<?php

require_once "Response.php";

abstract class Endpoint {
    protected $data;

    function __construct() {
        $this->internal_handle();    
    }
    
    abstract protected function handle();

    private function internal_handle() {
        $this->data = json_decode(file_get_contents("php://input"), true);

        $response = null;
        try {
            $response = $this->handle();
        } catch (Response $res) {
            $response = $res;
        } catch (Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $response = new ResponseError("Internal server error", 999);
        } catch (Throwable $t) {
            error_log($t);
            $response = new ResponseError("Internal server error", 998);
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