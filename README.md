# Pinglet
Pinglet is a high-performance, non-blocking, event-driven PHP framework built on top of the Swoole, Swow, and Workerman frameworks. It provides a simple yet powerful way to create HTTP APIs and web applications with support for route handling, environment configuration, logging, templating, and custom session management. The framework can be easily extended and customized to fit any project's needs, making it a versatile solution for creating scalable, efficient, and feature-rich APIs and web applications. Based on the wonderful [refortunato/SwooleApiHttpExample](https://github.com/refortunato/SwooleApiHttpExample).

## Requirements

* PHP 8.1 or higher
* Swoole, Swow, or Workerman installed and enabled
* Composer

## Components

- Logging.

- Database: [Pinga\Db](https://github.com/getpinga/db). For the Swoole version, you can also use [Pinglet\Db](https://github.com/getpinga/pinglet-db-swoole).

- Caching: [Pinga\Cache](https://github.com/getpinga/cache).

- Templates: [Twig](https://github.com/twigphp/Twig) or [Plates](https://github.com/thephpleague/plates).

- Support for .env files: required settings will be changed from .env file.

- Sessions.

- Cookies.

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

Pinglet API includes a basic logging class, **PiLogger**, that can be customized to fit your needs. To use the logger, choose the option you would like and replace ```$app['logger'] = new PiLogger(null, true);``` with one of those:

### Log to a file

```php
$app['logger'] = new PiLogger('/tmp/file.log');
```

### Log to stdout

```php
$app['logger'] = new PiLogger(null, true);
```

### Log to both file and stdout

```php
$app['logger'] = new PiLogger($logFilePath, true);
```

### Log action

```php
 $app['logger']->info("Log details");
 ```

The following log levels are available:

* error
* info
* debug

## Sessions

Pinglet API provides a custom session handling class, **PiSession**, that leverages the Pinga\Cache library for storing session data. This class ensures non-blocking session handling to maintain the performance benefits of Swoole, Swow, or Workerman.

Here's how to use the **PiSession** class in your project:

1. Add in the beginning of your start-xxx-http.php file:

```php
use App\PiSession;

$app['session'] = new PiSession();
$app['session']->start();
```

2. Include the **PiSession** class in your controller:

```php
use App\PiSession;
```

3. Add in the beginning of your controller:

```php
private $session;

public function __construct($app) {
    $this->session = $app['session'];
}
```

4. Set a session value:

```php
$this->session->set('key', 'value');
```

5. Get a session value:

```php
$value = $this->session->get('key', 'default_value');
```

Note: Ensure that the **/cache** directory exists and has the proper permissions to allow the session handler to read and write session data.

## Cookies

Cookies are a convenient way to store small amounts of data on the client-side, which can be useful for maintaining session state, tracking user preferences, and more. This guide will show you how to work with cookies in your controllers using the **PiCookie** class which provides two methods for working with cookies: **setCookie** and **getCookie**. You can use these methods in your controllers to set and get cookies easily.

### Set a Cookie

To set a cookie in a controller, use the **setCookie** method of the **PiCookie** class. This method takes the following parameters:

1. **$response**: The response object that you want to add the cookie to.
2. **$name**: The name of the cookie.
3. **$value**: The value of the cookie.
4. **$expire** (optional): The number of seconds until the cookie expires. The default value is **3600** seconds (1 hour).
5. **$path** (optional): The path on the server where the cookie will be available. The default value is **/**.
6. **$domain** (optional): The domain that the cookie is available to. The default value is an **empty** string.
7. **$secure** (optional): Whether the cookie should be transmitted only over a secure HTTPS connection. The default value is **false**.
8. **$httpOnly** (optional): Whether the cookie should be accessible only through the HTTP protocol. The default value is **true**.

Here's an example of how to use the setCookie method in a controller:

In the beginning of your controller method place:

```php
$cookie = new PiCookie();
```

Right before **$return** place:

```php
$response = $cookie->setCookie($response, 'cookie_name', 'cookie_value');
```

### Get a Cookie

To get the value of a cookie in a controller, use the **getCookie** method of the **PiCookie** class. This method takes the following parameters:

1. **$request**: The request object that contains the cookies.
2. **$name**: The name of the cookie.
3. **$default** (optional): The default value to return if the cookie is not found. The default value is **null**.

Here's an example of how to use the getCookie method in a controller:

In the beginning of your controller method place:

```php
$cookie = new PiCookie();
```

Change your response like:

```php
$cookieValue = $cookie->getCookie($request, 'cookie_name', 'default_value');
$responseBody = json_encode(['name' => 'Foo', 'cookieValue' => $cookieValue]);
$contentLength = strlen($responseBody);
```

## Static File Loader

The Static File Loader is a utility class that allows your application to serve static files, such as CSS, JavaScript, images, or other assets, from the **/public** directory. It is designed to work seamlessly with Swoole, Swow, and Workerman frameworks, ensuring non-blocking and efficient serving of static content. When a request is received for a static file, the **PiStaticFileHandler** class will handle it and return an appropriate response. If the file is not found, the request will continue to be processed by your application's other route handlers.

Note: Make sure your static files are placed in the **/public** directory, as this is the default location that the **PiStaticFileHandler** class will look for files.

## Contributing

We welcome contributions to improve and extend Pinglet. Please follow these steps to contribute:

1. Fork the repository.

2. Create a new branch for your changes.

3. Commit your changes with descriptive commit messages.

4. Push your changes to your fork.
 
5. Create a pull request describing your changes and referencing any related issues.

## License

Pinglet is released under the [MIT License](https://opensource.org/licenses/MIT). See the **LICENSE** file for more information.
