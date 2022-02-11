<?php

namespace hubertBlog;

class Response {
    public function write($data): Response {
        if (is_array($data) || is_object($data)) {
            var_dump($data);
            return $this;
        }

        echo $data;

        return $this;
    }

    public function withStatus(int $status): Response{
        http_response_code($status);

        return $this;
    }

    public function withCookie(string $key, string $value, ?array $options = array()): Response {
        if (!empty($options)) {
            setcookie(
                $key,
                $value,
                $options['expire'] ?? 0,
                $options['path'] ?? '',
                $options['domain'] ?? '',
                $options['secure'] ?? false,
                $options['httponly'] ?? false
            );

        }

        if (!isset($this->cookies[$key])) {
            setcookie($key, $value);
        }

        return $this;
    }

    public function json(array $data): Response {
        header('Content-Type: application/json; charset=utf-8');
        print(json_encode($data));

        return $this;
    }

    public function setHeader(string $key, string $value): Response {
        header("$key: $value");
        return $this;
    }
}