# Pinglet
Pinglet is a high-performance, non-blocking, event-driven PHP framework built on top of the Swoole, Swow, and Workerman frameworks. It provides a simple yet powerful way to create HTTP APIs and web applications with support for route handling, environment configuration, logging, templating, and custom session management. The framework can be easily extended and customized to fit any project's needs, making it a versatile solution for creating scalable, efficient, and feature-rich APIs and web applications. Based on the wonderful [refortunato/SwooleApiHttpExample](https://github.com/refortunato/SwooleApiHttpExample).

## Requirements

* PHP 8.1 or higher
* Swoole, Swow, or Workerman installed and enabled
* Composer

## Components

- [Logging](https://github.com/getpinga/pinglet/blob/main/docs/Logger.md).

- Database: [Pinga\Db](https://github.com/getpinga/db). For the Swoole version, you can also use [Pinglet\Db](https://github.com/getpinga/pinglet-db-swoole).

- Caching: [Pinga\Cache](https://github.com/getpinga/cache).

- [Templates](https://github.com/getpinga/pinglet/blob/main/docs/Templates.md): [Twig](https://github.com/twigphp/Twig) or [Plates](https://github.com/thephpleague/plates). Just install one of the two and follow the examples.

- Support for .env files: required settings will be changed from .env file.

- [Sessions](https://github.com/getpinga/pinglet/blob/main/docs/Sessions.md).

- [Cookies](https://github.com/getpinga/pinglet/blob/main/docs/Cookies.md).

## Installation

1. Make sure that Swoole, Swow, or Workerman are installed and enabled.

2. Create your application:

```bash
composer create-project --prefer-dist pinga/pinglet myapi
```

3. Copy the **sample-env** file to create a new **.env** file:

```bash
cp sample-env .env
```

## Configuration

Edit the .env file to configure the following environment variables:

* **HOST**: The host address for the server to bind to.
* **HOSTNAME**: The domain of the server.
* **PORT**: The port for the server to listen on.
* **PIDFILE**: The file path to store the process ID of the server.

## Basic Usage

Start the server using one of the following commands:

```bash
php start-swoole-http.php
php start-swow-http.php
php start-workerman-http.php
```

Access the API in your browser or via a REST client using the configured **HOSTNAME** and **PORT**.

## Routing

To define routes for the API, edit the **config/routes.php** file. Use the following methods to define routes:

* **get()**: Define a route for the **GET** HTTP method.
* **post()**: Define a route for the **POST** HTTP method.
* **put()**: Define a route for the **PUT** HTTP method.
* **delete()**: Define a route for the **DELETE** HTTP method.

## Controllers

Create a controller class in the **src/Controllers** directory. The controller class should include methods that handle specific route actions.

Use the **IndexController.php** as template to create a new controller.

## Templating

Pinglet API supports both Twig and Plates for templating.

To render a Twig template, use **TwigController.php** in a controller method.

To render a Plates template, use **PlatesController.php** in a controller method.

## Logging

Pinglet API includes a basic logging class, **PiLogger**, that can be customized to fit your needs. To use the logger, create a new instance and call the appropriate log level method:

```php
$logger = new PiLogger(null, true);
$logger->info('Log message');
```

The following log levels are available:

* emergency
* alert
* critical
* error
* warning
* notice
* info
* debug

## Sessions

Pinglet API provides a custom session handling class, **PiSession**, that leverages the Pinga\Cache library for storing session data. This class ensures non-blocking session handling to maintain the performance benefits of Swoole, Swow, or Workerman.

Here's how to use the **PiSession** class in your project:

1. Include the **PiSession** class in your controller:

```php
use App\PiSession;
```

2. Create a new instance of **PiSession**:

```php
$session = new PiSession();
```

3. Start a new session or resume an existing one:

```php
$session->start();
```

4. Set a session value:

```php
$session->set('key', 'value');
```

5. Get a session value:

```php
$value = $session->get('key', 'default_value');
```

Note: Ensure that the **/cache** directory exists and has the proper permissions to allow the session handler to read and write session data.

## Contributing

We welcome contributions to improve and extend Pinglet. Please follow these steps to contribute:

1. Fork the repository.

2. Create a new branch for your changes.

3. Commit your changes with descriptive commit messages.

4. Push your changes to your fork.
 
5. Create a pull request describing your changes and referencing any related issues.

## License

Pinglet is released under the [MIT License](https://opensource.org/licenses/MIT). See the **LICENSE** file for more information.

## TODO

- Static files.

- File uploading.

- Comments, security tests.
