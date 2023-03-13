<?php
declare(strict_types=1);
ini_set("memory_limit", "1G");

use App\PiLogger;
use FastRoute\Dispatcher;
use Nyholm\Psr7\ServerRequest;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;

require_once __DIR__ . "/vendor/autoload.php";
$routeCollection = require __DIR__ . "/config/routes.php";

$host = "0.0.0.0";
$hostname = "my.host.com";
$port = 8080;

$worker = new Worker("http://{$host}:{$port}");
$worker->count = 4;
$worker::$eventLoopClass = \Workerman\Events\Swoole::class;

$worker->onWorkerStart = function ($worker) use (
   $host, $port
) {
	if ($worker->id === 0) {
    $logger = new PiLogger(null, true);
    $logger->info("Pinglet Workerman running at http://{$host}:{$port}");
	}
};

$worker->onMessage = function ($connection, Request $request) use (
    $routeCollection, $hostname, $port
) {
    global $logger;
    $request_method = $request->method();
    $request_uri = $request->uri();
    $_SERVER["REQUEST_URI"] = $request_uri;
    $_SERVER["REQUEST_METHOD"] = $request_method;
    $_SERVER["REMOTE_ADDR"] = $request->header("remote_addr");
    $_GET = $request->get() ?: [];
    $_FILES = $request->file() ?: [];
    if (
        $request_method === "POST" &&
        $request->header("content-type") === "application/json"
    ) {
        $body = $request->rawBody();
        $_POST = empty($body) ? [] : json_decode($body, true);
    } else {
        $_POST = $request->post() ?: [];
    }

    $serverRequest = (new ServerRequest(
        method: $request_method,
        uri: $request_uri,
        headers: $request->header(),
        body: $request->rawBody(),
        serverParams: $_SERVER
    ))
        ->withQueryParams($_GET)
        ->withParsedBody($_POST)
        ->withUploadedFiles($_FILES);

    $routeInfo = $routeCollection->dispatch($request_method, $request_uri);

    $response = new Response();

    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $response->withStatus(404);
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $response->withStatus(405);
            $response->withHeader("Allow", implode(", ", $routeInfo[1]));
            break;
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            $serverRequest = $serverRequest->withQueryParams($vars);

            if (is_string($handler)) {
                [$controllerClass, $method] = explode("@", $handler);
                $controller = new $controllerClass();
                $serverResponse = $controller->$method($serverRequest);
            } elseif (is_array($handler)) {
                [$controllerClass, $method] = $handler;
                $controller = new $controllerClass();
                $serverResponse = $controller->$method($serverRequest);
            } else {
                $serverResponse = $handler($serverRequest);
            }

            foreach ($serverResponse->getHeaders() as $header => $values) {
                if (strtolower($header) === "location") {
                    $response->withHeader("Location", $values[0]);
                }
                foreach ($values as $value) {
                    $response->withHeader($header, $value);
                }
            }

            $response->withStatus($serverResponse->getStatusCode());
            $response->withBody((string) $serverResponse->getBody());
            break;
    }

    $connection->send($response);
};

Worker::runAll();
