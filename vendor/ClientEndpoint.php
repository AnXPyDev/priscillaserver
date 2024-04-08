<?php

require_once "Endpoint.php";
require_once "Response.php";
require_once "Auth.php";

abstract class ClientEndpoint extends Endpoint {
    protected $client;

    abstract protected function handle_client();

    final protected function handle() {
        global $auth;

        $secret = $this->data["secret"] ?? null;
        if (is_null($secret)) {
            return new ResponseError("No secret provided");
        }

        $this->client = $auth->get_client($secret);

        if (is_null($this->client)) {
            return new ResponseError("Invalid secret");
        }

        return $this->handle_client();
    }
}