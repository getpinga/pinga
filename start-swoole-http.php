<?php
declare(strict_types=1);
ini_set('memory_limit', '1G');

use App\PiLogger;
use App\Routes\Router;
use Nyholm\Psr7\ServerRequest;

require_once __DIR__ . '/vendor/autoload.php';
$routeCollection = require __DIR__ . '/config/routes.php';

$host = "0.0.0.0";
$hostname = "my.host.com";
$port = 8080;

$server = new Swoole\HTTP\Server($host , $port);

$server->set([
    'worker_num' => 4,      // The number of worker processes to start
    'backlog' => 128,       // TCP backlog connection number
    'input_buffer_size' => 32 * 1024*1024, //Configure the memory size of the server receive input buffer. Default value is 2M. Value in bytes (in this example: 32MB).
    'buffer_output_size' => 32 * 1024*1024, //Set the output memory buffer server send size. The default value is 2M. Value in bytes (in this example: 32MB).
]);

// Choose one
// Log to a file
//$logger = new PiLogger('/tmp/file.log');
// Log to stdout
$logger = new PiLogger(null, true);
// Log to both file and stdout
//$logger = new PiLogger($logFilePath, true);

$router = new Router();
$router->setRouteCollector($routeCollection);

$server->on("WorkerStart", function($server, $workerId) use ($logger)
{
	$logger->info("New worker started: {$workerId}");
});

$server->on('start', function (Swoole\HTTP\Server $server) use ($hostname, $port, $logger) {
	$logger->info(sprintf('Pinglet Swoole running at http://%s:%s', $hostname, $port));
});

$server->on('Request', function(Swoole\HTTP\Request $request, Swoole\HTTP\Response $response) use ($router, $logger)
{
    $request_method = $request->server['request_method'];
    $request_uri = $request->server['request_uri'];
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['REQUEST_METHOD'] = $request_method;
    $_SERVER['REMOTE_ADDR'] = $request->server['remote_addr'];
    $_GET = $request->get ?? [];
    $_FILES = $request->files ?? [];
	
    $serverRequest = (new ServerRequest(
        method: $request->getMethod(),
        uri: $request->server['request_uri'],
        headers: $request->header,
        body: $request->getContent(), //$request->getData(),
        serverParams: $request->server
    ))->withQueryParams($request->get ?? [])
        ->withParsedBody($request->post ?? [])
        ->withUploadedFiles($request->files ?? []);

    $serverReponse = $router->execute($serverRequest);

    foreach ($serverReponse->getHeaders() as $header => $values) {
        if (strtolower($header) === 'location') {
            $response->redirect($values[0]);
        }
        foreach ($values as $value) {
            $response->header($header, $value);
        }
    }
    $response->status($serverReponse->getStatusCode());
    $response->end((string) $serverReponse->getBody());
});

$server->on("Shutdown", function($server, $workerId) use ($logger)
{
	$logger->info("Server is shutdown: {$workerId}");
});

$server->on("WorkerStop", function($server, $workerId) use ($logger)
{
	$logger->info("Worker stoped: {$workerId}");
});

$server->start();
