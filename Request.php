<?php

namespace EasyRouter;

class Request {
    public array $body;
    public array $query;
    public array $cookies;
    public array $params;
    public array $headers;

    public function __construct(array $params = array()) {
        $this->body = $_POST;
        $this->query = $_GET;
        $this->cookies = $_COOKIE;
        $this->params = $params;
        $this->headers = getallheaders();
    }
}
