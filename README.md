# Pinglet
A lightweight and high-performance PHP microframework that harnesses the power of Swoole or Swow for lightning-fast web apps, while maintaining its simplicity and ease of use. Based on the wonderful [refortunato/SwooleApiHttpExample](https://github.com/refortunato/SwooleApiHttpExample).

## Installation

1. Install Swoole or Swow extensions.

2. Install app using

```
composer create-project --prefer-dist pinga/pinglet myapi
```

3. Edit **start-swoole-http.php** or **start-swow-http.php** and add your IP, hostname and port. Start by running the chosen file.

## Components

- Logging: use the PiLogger class.

- Database: [Pinga\Db](https://github.com/getpinga/db). For the Swoole version, you can also use [Pinglet\Db](https://github.com/getpinga/pinglet-db-swoole).

- Caching: [Pinga\Cache](https://github.com/getpinga/cache).

## TODO

- .env files support.

- PHP-DI and Respect/Validation support.

- Sessions and cookies.

- Monitoring tools.

- Comments, documentation, security tests.
