<?php

namespace EasyRouter;

class Router {
    private static array $stack = array();
    private static array $middlewareStack = array();
    private static array $settings = array(
        "JSON" => false,
    );

    public static function use($setting): void {
        self::$settings[$setting[0]] = $setting[1];
    }

    public static function JSON(): array {
        return ["JSON", true];
    }

    private static function addEndpointToStack(string $method, string $endpoint, array $middleware, callable $callback){
        if ($endpoint[0] === "/") $endpoint = substr($endpoint, 1);
        if (strlen($endpoint > 0) && $endpoint[-1] === "/") $endpoint = substr($endpoint, 0, -1);
        $endpoint = trim($endpoint);

        self::$stack[] = array(
            "ROUTE" => new Route(strtoupper($method), $endpoint),
            "CALLBACK" => $callback,
            "MIDDLEWARE" => $middleware
        );
    }

    public static function GET(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack("GET", $endpoint, $middleware, $callback);
    }

    public static function POST(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack("POST", $endpoint, $middleware, $callback);
    }

    public static function DELETE(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack("DELETE", $endpoint, $middleware, $callback);
    }

    public static function PATCH(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack("PATCH", $endpoint, $middleware, $callback);
    }

    public static function ANY(string $endpoint, array $middleware, callable $callback): void {
        self::addEndpointToStack("PATCH", $endpoint, $middleware, $callback);
    }

    public static function listen(): void {
        $actPath = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['PHP_SELF']);
        $actPath = trim($actPath);
        if ($actPath && $actPath[0] === "/") $actPath = substr($actPath, 1);
        if (strlen($actPath) > 0 && $actPath[-1] === "/") $actPath = substr($actPath, 0, -1);

        $method = $_SERVER['REQUEST_METHOD'];

        if (count($_GET) > 0) {
            $actPath = explode("?", $actPath)[0]."/";
        }

        // LISTEN ROUTES
        foreach (self::$stack as $route) {
            $endpoint = $route['ROUTE']->getEndpoint();
            $middlewareStack = $route['MIDDLEWARE'];
            $params = array();

            $explodedEndpoint = explode("/", $endpoint);
            $explodedActualPath = explode("/", $actPath);

            for ($i=0; $i<count($explodedEndpoint); $i++) {
                if (@$explodedEndpoint[$i][0] === ':') {
                    $ascIndex = substr($explodedEndpoint[$i], 1);
                    $params[$ascIndex] = $explodedActualPath[$i];
                    $explodedActualPath[$i] = $explodedEndpoint[$i];
                }
            }

            $actPathWithoutParamsValues = implode('/', $explodedActualPath);
            $isMiddlewareThrowNext = true;

            $req = new Request($params);
            $res = new Response();

            if (count($middlewareStack) > 0) {
                foreach ($middlewareStack as $middleware) {
                    if (!is_callable($middleware) || !is_string($middleware)) {
                        $type = gettype($middleware);
                        throw new \TypeError("Middleware must be callable, passed $type");
                    }

                    $middlewareResponse = call_user_func($middleware, $req, $res);
                    if (!$middlewareResponse) {
                        $isMiddlewareThrowNext = false;
                        break;
                    }

                    ob_end_clean();
                }
            }

            if ($isMiddlewareThrowNext && $actPathWithoutParamsValues === $endpoint && $method === $route['ROUTE']->getMethod()) {
                if (self::$settings['JSON']) {
                    $body = json_decode(file_get_contents('php://input'));
                    if ($body) $req->body = (array)$body;
                }

                $route['CALLBACK']($req, $res);

                break;
            }
        }
    }
}
