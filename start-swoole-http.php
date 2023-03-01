<?php
declare(strict_types=1);
ini_set('memory_limit', '1G');

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
    'task_worker_num' => 4,  // The amount of task workers to start
    'backlog' => 128,       // TCP backlog connection number
    'input_buffer_size' => 32 * 1024*1024, //Configure the memory size of the server receive input buffer. Default value is 2M. Value in bytes (in this example: 32MB).
    'buffer_output_size' => 32 * 1024*1024, //Set the output memory buffer server send size. The default value is 2M. Value in bytes (in this example: 32MB).
]);

$router = new Router();
$router->setRouteCollector($routeCollection);

// Triggered when new worker processes starts
$server->on("WorkerStart", function($server, $workerId)
{
    echo PHP_EOL."New worker started: {$workerId}".PHP_EOL;
});

$server->on('Task', function (Swoole\Server $server, $task_id, $reactorId, $data)
{
    echo "Task Worker Process received data";
    echo "#{$server->worker_id}\tonTask: [PID={$server->worker_pid}]: task_id=$task_id, data_len=" . strlen($data) . "." . PHP_EOL;
    $server->finish($data);
});

// Triggered when the HTTP Server starts, connections are accepted after this callback is executed
$server->on('start', function (Swoole\HTTP\Server $server) use ($hostname, $port) {
    echo sprintf('Swoole http server is started at http://%s:%s' . PHP_EOL, $hostname, $port);
});

// The main HTTP server request callback event, entry point for all incoming HTTP requests
$server->on('Request', function(Swoole\HTTP\Request $request, Swoole\HTTP\Response $response) use ($router)
{
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

// Triggered when the server is shutting down
$server->on("Shutdown", function($server, $workerId)
{
    echo PHP_EOL."Server is shutdown: {$workerId}".PHP_EOL;
});

// Triggered when worker processes are being stopped
$server->on("WorkerStop", function($server, $workerId)
{
    echo PHP_EOL."Worker stoped: {$workerId}".PHP_EOL;
});

$server->start();
