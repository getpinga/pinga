# Logger

Choose the options you would like and replace ```$app['logger'] = new PiLogger(null, true);``` with one of those:

## Log to a file

```
$app['logger'] = new PiLogger('/tmp/file.log');
```

## Log to stdout

```
$app['logger'] = new PiLogger(null, true);
```

## Log to both file and stdout

```
$app['logger'] = new PiLogger($logFilePath, true);
```
