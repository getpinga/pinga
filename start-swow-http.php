<?php
declare(strict_types=1);
ini_set('memory_limit', '1G');

use Swow\Coroutine;
use Swow\CoroutineException;
use Swow\Errno;
use Swow\Http\Protocol\ProtocolException as HttpProtocolException;
use Swow\Http\Status as HttpStatus;
use Swow\Psr7\Psr7;
use Swow\Psr7\Server\Server as HttpServer;
use Swow\Socket;
use Swow\SocketException;
use App\Routes\Router;
use Nyholm\Psr7\ServerRequest;

require_once __DIR__ . '/vendor/autoload.php';
$routeCollection = require __DIR__ . '/config/routes.php';

$host = "0.0.0.0";
$hostname = "my.host.com";
$port = 8081;

$server = new \Swow\Psr7\Server\Server();
$server->bind($host, $port)->listen(Socket::DEFAULT_BACKLOG);

echo sprintf('Swow http server is started at http://%s:%s' . PHP_EOL, $hostname, $port);

$router = new Router();
$router->setRouteCollector($routeCollection);

while (true) {
    try {
        $connection = null;
        $connection = $server->acceptConnection();
        Coroutine::run(static function () use ($connection, $router): void {
            try {
                while (true) {
                    $request = null;
                    try {
			$request = $connection->recvHttpRequest();
			$serverRequest = (new ServerRequest(
                            method: $request->getMethod(),
                            uri: $request->getUri(),
                            headers: $request->getStandardHeaders(),
                            body: $request->getBody(),
                            serverParams: $request->getServerParams()
                        ))->withQueryParams($request->getQueryParams() ?? [])
                          ->withParsedBody($request->post ?? [])
                          ->withUploadedFiles($request->files ?? []);
                        $serverReponse = $router->execute($serverRequest);
			$connection->respond($serverReponse->getBody(), $serverReponse->getStatusCode());
                    } catch (HttpProtocolException $exception) {
			echo sprintf('Error: %s' . PHP_EOL, $exception->getMessage());
                        $connection->error($exception->getCode(), $exception->getMessage(), close: true);
                        break;
                    }
                    if (!$connection->shouldKeepAlive()) {
                        break;
                    }
                }
            } catch (Exception $exception) {
                echo sprintf('Error: %s' . PHP_EOL, $exception->getMessage());
            } finally {
                $connection->close();
            }
        });
    } catch (SocketException|CoroutineException $exception) {
        if (in_array($exception->getCode(), [Errno::EMFILE, Errno::ENFILE, Errno::ENOMEM], true)) {
            sleep(1);
        } else {
            break;
        }
    }
}
