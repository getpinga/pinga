<?php

namespace App;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\GroupHandler;

class PiLogger {
    private $logger;

    public function __construct($logFilePath = null, $useStdout = false) {
        $handlers = [];

        if ($logFilePath !== null) {
            $fileHandler = new StreamHandler($logFilePath, Logger::DEBUG);
            $handlers[] = $fileHandler;
        }

        if ($useStdout) {
            $stdoutHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::DEBUG);
            $handlers[] = $stdoutHandler;
        }

        if (count($handlers) === 0) {
            throw new \InvalidArgumentException('At least one handler must be specified.');
        }

        if (count($handlers) === 1) {
            $this->logger = new Logger('PINGLET', $handlers);
        } else {
            $groupHandler = new GroupHandler($handlers);
            $this->logger = new Logger('PINGLET', [$groupHandler]);
        }
    }

    public function debug($message) {
        $this->logger->debug($message);
    }

    public function info($message) {
        $this->logger->info($message);
    }

    public function notice($message) {
        $this->logger->notice($message);
    }

    public function warning($message) {
        $this->logger->warning($message);
    }

    public function error($message) {
        $this->logger->error($message);
    }

    public function critical($message) {
        $this->logger->critical($message);
    }

    public function alert($message) {
        $this->logger->alert($message);
    }

    public function emergency($message) {
        $this->logger->emergency($message);
    }
}
