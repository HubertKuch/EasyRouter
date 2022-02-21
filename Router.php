<?php

namespace EasyRouter;

class Router {
    private static array $stack = array();
    private static array $settings = array(
        "JSON" => false,
    );

    public static function use($setting): void {
        self::$settings[$setting[0]] = $setting[1];
    }

    public static function middleware($middleware, callable $callback): void {
        $isCanRun = true;

        if (is_callable($middleware)) {
            if (!call_user_func($middleware)) {
                $isCanRun = false;
            }
        } else if (is_array($middleware)) {
            foreach ($middleware as $mid) {
                if(!call_user_func($mid)) {
                    $isCanRun = false;
                }
            }
        }

        if ($isCanRun){
            call_user_func($callback,  new Request(), new Response());
        }
    }

    public static function JSON(): array {
        return ["JSON", true];
    }

    private static function addEndpointToStack(string $method, string $endpoint, callable $callback){
        if ($endpoint[0] === "/") $endpoint = substr($endpoint, 1);
        if (strlen($endpoint > 0) && $endpoint[-1] === "/") $endpoint = substr($endpoint, 0, -1);
        $endpoint = trim($endpoint);

        self::$stack[] = array(
            "ROUTE" => new Route(strtoupper($method), $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function GET(string $endpoint, callable $callback): void {
        self::addEndpointToStack("GET", $endpoint, $callback);
    }

    public static function POST(string $endpoint, $callback): void {
        self::addEndpointToStack("POST", $endpoint, $callback);
    }

    public static function DELETE(string $endpoint, $callback): void {
        self::addEndpointToStack("DELETE", $endpoint, $callback);

    }
    public static function PATCH(string $endpoint, $callback): void {
        self::addEndpointToStack("PATCH", $endpoint, $callback);
    }

    public static function listen(): void {
        $actPath = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['PHP_SELF']);
        $actPath = trim($actPath);
        if ($actPath[0] === "/") $actPath = substr($actPath, 1);
        if (strlen($actPath) > 0 && $actPath[-1] === "/") $actPath = substr($actPath, 0, -1);

        $method = $_SERVER['REQUEST_METHOD'];

        if (count($_GET) > 0) {
            $actPath = explode("?", $actPath)[0]."/";
        }

        foreach (self::$stack as $route) {
            $endpoint = $route['ROUTE']->getEndpoint();

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

            if ($actPathWithoutParamsValues === $endpoint && $method === $route['ROUTE']->getMethod()) {
                $req = new Request($params);
                $res = new Response();

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
