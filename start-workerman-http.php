<?php
declare(strict_types=1);
ini_set("memory_limit", "1G");

use App\PiLogger;
use App\PiStaticFileHandler;
use FastRoute\Dispatcher;
use Nyholm\Psr7\ServerRequest;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Worker;
use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";
$app['route'] = require __DIR__ . "/config/routes.php";

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['HOST'];
$hostname = $_ENV['HOSTNAME'];
$port = intval($_ENV['PORT']);

$worker = new Worker("http://{$host}:{$port}");
$worker->count = 4;
$worker::$eventLoopClass = \Workerman\Events\Swoole::class;

$app['staticFileHandler'] = new PiStaticFileHandler(__DIR__ . '/public');

$worker->onWorkerStart = function ($worker) use (
   $host, $port, $app
) {
	if ($worker->id === 0) {
    $app['logger'] = new PiLogger(null, true);
    $app['logger']->info("Pinga Workerman running at http://{$host}:{$port}");
	}
};

$worker->onMessage = function ($connection, Request $request) use (
    $app, $hostname, $port
) {
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
        ->withUploadedFiles($_FILES)
        ->withCookieParams($request->cookie() ?? []);
	
    $response = new Response();
	
    $staticResponse = $app['staticFileHandler']->handleRequest($serverRequest);
    if ($staticResponse !== null) {
        foreach ($staticResponse->getHeaders() as $header => $values) {
            if (strtolower($header) === "location") {
                $response->withHeader("Location", $values[0]);
            }
            foreach ($values as $value) {
                $response->withHeader($header, $value);
            }
        }
        $response->withStatus($staticResponse->getStatusCode());
        $response->withBody((string) $staticResponse->getBody());
        $connection->send($response);
        return;
    }

    $routeInfo = $app['route']->dispatch($request_method, $request_uri);

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
                $controller = new $controllerClass($app);
                $serverResponse = $controller->$method($serverRequest);
            } elseif (is_array($handler)) {
                [$controllerClass, $method] = $handler;
                $controller = new $controllerClass($app);
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
