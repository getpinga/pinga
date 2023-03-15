<?php
declare(strict_types=1);
ini_set("memory_limit", "1G");

use App\PiLogger;
use App\PiStaticFileHandler;
use FastRoute\Dispatcher;
use Nyholm\Psr7\ServerRequest;
use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";
$app['route'] = require __DIR__ . "/config/routes.php";

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['HOST'];
$hostname = $_ENV['HOSTNAME'];
$port = intval($_ENV['PORT']);

$server = new Swoole\HTTP\Server($host, $port);

$server->set([
    "worker_num" => 4, // The number of worker processes to start
    "backlog" => 128, // TCP backlog connection number
    "input_buffer_size" => 32 * 1024 * 1024, //Configure the memory size of the server receive input buffer. Default value is 2M. Value in bytes (in this example: 32MB).
    "buffer_output_size" => 32 * 1024 * 1024, //Set the output memory buffer server send size. The default value is 2M. Value in bytes (in this example: 32MB).
    "pid_file" => $_ENV['PIDFILE']
]);

$app['logger'] = new PiLogger(null, true);
$app['staticFileHandler'] = new PiStaticFileHandler(__DIR__ . '/public');

$server->on("WorkerStart", function ($server, $workerId) use ($app) {
    $app['logger']->info("New worker started: {$workerId}");
});

$server->on("start", function (Swoole\HTTP\Server $server) use (
    $hostname,
    $port,
    $app
) {
    $app['logger']->info(
        sprintf("Pinglet Swoole running at http://%s:%s", $hostname, $port)
    );
});

$server->on("Request", function (
    Swoole\HTTP\Request $request,
    Swoole\HTTP\Response $response
) use ($app) {
    $request_method = $request->server["request_method"];
    $request_uri = $request->server["request_uri"];
    $_SERVER["REQUEST_URI"] = $request_uri;
    $_SERVER["REQUEST_METHOD"] = $request_method;
    $_SERVER["REMOTE_ADDR"] = $request->server["remote_addr"];
    $_GET = $request->get ?? [];
    $_FILES = $request->files ?? [];
    if ($request_method === 'POST' && $request->header['content-type'] === 'application/json') {
        $body = $request->rawContent();
        $_POST = empty($body) ? [] : json_decode($body);
    } else {
        $_POST = $request->post ?? [];
    }

    $serverRequest = (new ServerRequest(
        method: $request->getMethod(),
        uri: $request->server["request_uri"],
        headers: $request->header,
        body: $request->getContent(), //$request->getData(),
        serverParams: $request->server
    ))
        ->withQueryParams($request->get ?? [])
        ->withParsedBody($request->post ?? [])
        ->withUploadedFiles($request->files ?? [])
        ->withCookieParams($request->cookie ?? []);
    
	$staticResponse = $app['staticFileHandler']->handleRequest($serverRequest);
	if ($staticResponse !== null) {
		foreach ($staticResponse->getHeaders() as $header => $values) {
			foreach ($values as $value) {
				$response->header($header, $value);
			}
		}
		$response->status($staticResponse->getStatusCode());
		$response->end((string) $staticResponse->getBody());
		return;
	}

    $routeInfo = $app['route']->dispatch($request_method, $request_uri);

    switch ($routeInfo[0]) {
        case Dispatcher::NOT_FOUND:
            $response->status(404);
            $response->end();
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            $response->status(405);
            $response->header("Allow", implode(", ", $routeInfo[1]));
            $response->end();
            break;
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            $serverRequest = $serverRequest->withQueryParams($vars);

            if (is_string($handler)) {
                [$controllerClass, $method] = explode("@", $handler);
                $controller = new $controllerClass($app);
                $serverReponse = $controller->$method($serverRequest);
            } elseif (is_array($handler)) {
                [$controllerClass, $method] = $handler;
                $controller = new $controllerClass($app);
                $serverReponse = $controller->$method($serverRequest);
            } else {
                $serverReponse = $handler($serverRequest);
            }

            foreach ($serverReponse->getHeaders() as $header => $values) {
                if (strtolower($header) === "location") {
                    $response->redirect($values[0]);
                }
                foreach ($values as $value) {
                    $response->header($header, $value);
                }
            }

            $response->status($serverReponse->getStatusCode());
            $response->end((string) $serverReponse->getBody());
            break;
    }
});

$server->on("Shutdown", function ($server, $workerId) use ($app) {
    $app['logger']->info("Server is shutdown: {$workerId}");
});

$server->on("WorkerStop", function ($server, $workerId) use ($app) {
    $app['logger']->info("Worker stoped: {$workerId}");
});

$server->start();
