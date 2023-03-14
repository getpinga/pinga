# Logger

Choose the options you would like and replace ```php $app['logger'] = new PiLogger(null, true);``` with one of those:

## Log to a file

```php
$app['logger'] = new PiLogger('/tmp/file.log');
```

## Log to stdout

```php
$app['logger'] = new PiLogger(null, true);
```

## Log to both file and stdout

```php
$app['logger'] = new PiLogger($logFilePath, true);
```
