# Pinglet
A lightweight and high-performance PHP microframework that harnesses the power of Swoole, Swow or Workerman for lightning-fast web apps, while maintaining its simplicity and ease of use. Based on the wonderful [refortunato/SwooleApiHttpExample](https://github.com/refortunato/SwooleApiHttpExample).

## Installation

1. Install Swoole/Swow extension or Workerman package.

2. Install app using

```
composer create-project --prefer-dist pinga/pinglet myapi
```

3. Edit **sample-env** and add your IP, hostname and port, rename it to **.env** and run one of the **start-swoole-http.php**, **start-swow-http.php** or **start-workerman-http.php**, depending on your choice.

## Components

- [Logging](https://github.com/getpinga/pinglet/blob/main/docs/Logger.md).

- Database: [Pinga\Db](https://github.com/getpinga/db). For the Swoole version, you can also use [Pinglet\Db](https://github.com/getpinga/pinglet-db-swoole).

- Caching: [Pinga\Cache](https://github.com/getpinga/cache).

- [Templates](https://github.com/getpinga/pinglet/blob/main/docs/Templates.md): [Twig](https://github.com/twigphp/Twig) or [Plates](https://github.com/thephpleague/plates). Just install one of the two and follow the examples.

- Support for .env files: required settings will be changed from .env file.

- [Sessions](https://github.com/getpinga/pinglet/blob/main/docs/Sessions.md).

- [Cookies](https://github.com/getpinga/pinglet/blob/main/docs/Cookies.md).

## TODO

- Static files.

- File uploading.

- Comments, security tests.
