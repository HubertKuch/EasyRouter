<?php

namespace EasyRouter;

class Router {
    private static array $stack = array();

    public static function GET(string $endpoint, callable $callback) {
        self::$stack[] = array(
            "ROUTE" => new Route("GET", $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function POST(string $endpoint, callable $callback) {
        self::$stack[] = array(
            "ROUTE" => new Route("POST", $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function DELETE(string $endpoint, callable $callback) {
        self::$stack[] = array(
            "ROUTE" => new Route("DELETE", $endpoint),
            "CALLBACK" => $callback
        );
    }
    public static function PATCH(string $endpoint, callable $callback) {
        self::$stack[] = array(
            "ROUTE" => new Route("PATCH", $endpoint),
            "CALLBACK" => $callback
        );
    }

    public static function listen() {
        $actPath = trim(str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['PHP_SELF']));
        $method = $_SERVER['REQUEST_METHOD'];

        if (count($_GET) > 0) {
            $actPath = explode("?", $actPath)[0]."/";
        }


        foreach (self::$stack as $route) {
            $params = array();
            $endpoint = $route['ROUTE']->getEndpoint();
            $explodedEndpoint = explode("/", $endpoint);
            $explodedActualPath = explode("/", $actPath);

            if (strpos($endpoint, ':')) {
                for ($i=0; $i < count($explodedEndpoint); $i++) {
                    if (!empty($explodedActualPath[$i]) && @$explodedEndpoint[$i][0] == ":") {
                        $params[substr($explodedEndpoint[$i], 1)] = $explodedActualPath[$i];
                        $explodedActualPath[$i] = $explodedEndpoint[$i];
                    }
                }
            }

            $actPath = implode(
                "/",
                $explodedActualPath
                ).($actPath[strlen($actPath)-1] !== '/' ?'/':'');

            if ($actPath === $endpoint && $method === $route['ROUTE']->getMethod()) {
                $req = new Request($params);
                $res = new Response();

                $route['CALLBACK']($req, $res);

                break;
            }
        }
    }
}
