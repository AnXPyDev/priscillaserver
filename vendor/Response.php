<?php

interface Response {
    function getCode(): int;
    function getData(): mixed;
}

class ResponseSuccess implements Response {
    function __construct(public array $data = array()) {}

    function getCode(): int { return 0; }
    function getData(): array { return $this->data; }
}

class ResponseError implements Response {
    function __construct(public String $message, public int $code = 1) {}

    function getCode(): int { return $this->code; }
    function getData(): array { return array("message" => $this->message); }
}