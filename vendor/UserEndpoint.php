<?php

require_once "Endpoint.php";
require_once "Response.php";
require_once "Auth.php";

abstract class UserEndpoint extends Endpoint {
    protected $user;

    abstract protected function handle_user();

    final protected function handle() {
        global $auth;

        $session = $this->data["session"] ?? null;
        if (is_null($session)) {
            return new ResponseError("Not logged in");
        }

        $this->user = $auth->get_user($session);

        if (is_null($this->user)) {
            return new ResponseError("Invalid session");
        }

        return $this->handle_user();
    }
}