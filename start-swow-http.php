<?php
declare(strict_types=1);
ini_set("memory_limit", "1G");

use Swow\Coroutine;
use Swow\CoroutineException;
use Swow\Errno;
use Swow\Http\Protocol\ProtocolException as HttpProtocolException;
use Swow\Http\Status as HttpStatus;
use Swow\Psr7\Psr7;
use Swow\Psr7\Server\Server as HttpServer;
use Swow\Socket;
use Swow\SocketException;
use App\PiLogger;
use FastRoute\Dispatcher;
use Nyholm\Psr7\ServerRequest;

require_once __DIR__ . "/vendor/autoload.php";
$routeCollection = require __DIR__ . "/config/routes.php";

$host = "0.0.0.0";
$hostname = "my.host.com";
$port = 8081;

$server = new \Swow\Psr7\Server\Server();
$server->bind($host, $port)->listen(Socket::DEFAULT_BACKLOG);

// Choose one
// Log to a file
//$logger = new PiLogger('/tmp/file.log');
// Log to stdout
$logger = new PiLogger(null, true);
// Log to both file and stdout
//$logger = new PiLogger($logFilePath, true);

$logger->info(
    sprintf("Pinglet Swow running at http://%s:%s", $hostname, $port)
);

while (true) {
    try {
        $connection = null;
        $connection = $server->acceptConnection();
        Coroutine::run(static function () use (
            $connection,
            $routeCollection,
            $logger
        ): void {
            try {
                while (true) {
                    $request = null;
                    try {
                        $request = $connection->recvHttpRequest();
                        $request_method = $request->getMethod();
                        $request_uri = $request->getUri()->getPath();
                        $_SERVER["REQUEST_URI"] = $request_uri;
                        $_SERVER["REQUEST_METHOD"] = $request_method;
                        $_SERVER["REMOTE_ADDR"] = $request->getServerParams()[
                            "remote_addr"
                        ];
                        $_GET = $request->get ?? [];
                        $_FILES = $request->files ?? [];
                        if ($request_method === 'POST' && $request->getHeaderLine('content-type') === 'application/json') {
                            $body = $request->getBody();
                            $_POST = empty($body) ? [] : json_decode($body);
                        } else {
                            $_POST = $request->getBody();
                        }

                        $serverRequest = (new ServerRequest(
                            method: $request->getMethod(),
                            uri: $request->getUri()->getPath(),
                            headers: $request->getStandardHeaders(),
                            body: $request->getBody(),
                            serverParams: $request->getServerParams()
                        ))
                            ->withQueryParams($request->getQueryParams() ?? [])
                            ->withParsedBody($request->post ?? [])
                            ->withUploadedFiles($request->files ?? []);

                        $routeInfo = $routeCollection->dispatch(
                            $request_method,
                            $request_uri
                        );

                        switch ($routeInfo[0]) {
                            case Dispatcher::NOT_FOUND:
                                $connection->error(HttpStatus::NOT_FOUND);
                                break;
                            case Dispatcher::METHOD_NOT_ALLOWED:
                                $connection->error(HttpStatus::NOT_ALLOWED);
                                break;
                            case Dispatcher::FOUND:
                                $handler = $routeInfo[1];
                                $vars = $routeInfo[2];
                                $serverRequest = $serverRequest->withQueryParams(
                                    $vars
                                );

                                if (is_string($handler)) {
                                    [$controllerClass, $method] = explode(
                                        "@",
                                        $handler
                                    );
                                    $controller = new $controllerClass();
                                    $serverReponse = $controller->$method(
                                        $serverRequest
                                    );
                                } elseif (is_array($handler)) {
                                    [$controllerClass, $method] = $handler;
                                    $controller = new $controllerClass();
                                    $serverReponse = $controller->$method(
                                        $serverRequest
                                    );
                                } else {
                                    $serverReponse = $handler($serverRequest);
                                }

                                if (
                                    $serverReponse instanceof
                                    ResponsePlusInterface
                                ) {
                                    $headers = $serverReponse->getStandardHeaders();
                                } else {
                                    $headers[
                                        "Connection"
                                    ] = $connection->shouldKeepAlive()
                                        ? "keep-alive"
                                        : "closed";
                                    if (
                                        !$serverReponse->hasHeader(
                                            "Content-Length"
                                        )
                                    ) {
                                        $body = (string) $serverReponse->getBody();
                                        $headers["Content-Length"] = strlen(
                                            $body
                                        );
                                    }
                                }

                                $serverReponse = Psr7::setHeaders(
                                    $serverReponse,
                                    $headers
                                );
                                $connection->sendHttpResponse($serverReponse);
                                break;
                        }
                    } catch (HttpProtocolException $exception) {
                        $logger->error($exception->getMessage());
                        $connection->error(
                            $exception->getCode(),
                            $exception->getMessage(),
                            close: true
                        );
                        break;
                    }
                    if (!$connection->shouldKeepAlive()) {
                        break;
                    }
                }
            } catch (Exception $exception) {
                $logger->error($exception->getMessage());
            } finally {
                $connection->close();
            }
        });
    } catch (SocketException | CoroutineException $exception) {
        if (
            in_array(
                $exception->getCode(),
                [Errno::EMFILE, Errno::ENFILE, Errno::ENOMEM],
                true
            )
        ) {
            sleep(1);
        } else {
            break;
        }
    }
}
