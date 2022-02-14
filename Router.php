<?php

namespace hubertBlog;

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

    public static function GET(string $endpoint, $callback): void {
        self::$stack[] = array(
            "ROUTE" => new Route("GET", $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function POST(string $endpoint, $callback): void {
        self::$stack[] = array(
            "ROUTE" => new Route("POST", $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function DELETE(string $endpoint, $callback): void {
        self::$stack[] = array(
            "ROUTE" => new Route("DELETE", $endpoint),
            "CALLBACK" => $callback
        );
    }
    public static function PATCH(string $endpoint, $callback): void {
        self::$stack[] = array(
            "ROUTE" => new Route("PATCH", $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function listen(): void {
        $actPath = trim(str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['PHP_SELF']));
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
