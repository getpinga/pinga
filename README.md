# Pinglet
A lightweight and high-performance PHP microframework that harnesses the power of Swoole, Swow or Workerman for lightning-fast web apps, while maintaining its simplicity and ease of use. Based on the wonderful [refortunato/SwooleApiHttpExample](https://github.com/refortunato/SwooleApiHttpExample).

## Installation

1. Install Swoole/Swow extension or Workerman package.

2. Install app using

```
composer create-project --prefer-dist pinga/pinglet myapi
```

3. Edit **start-swoole-http.php**, **start-swow-http.php** or **start-workerman-http.php** and add your IP, hostname and port. Start by running the chosen file.

## Components

- Logging: use the PiLogger class.

- Database: [Pinga\Db](https://github.com/getpinga/db). For the Swoole version, you can also use [Pinglet\Db](https://github.com/getpinga/pinglet-db-swoole).

- Caching: [Pinga\Cache](https://github.com/getpinga/cache).

- Templates: [Twig](https://github.com/twigphp/Twig) or [Plates](https://github.com/thephpleague/plates). Just install one of the two and follow the examples.

## TODO

- .env files support and respect/validation support.

- Sessions and cookies.

- Status monitoring tool.

- Comments, documentation, security tests.
