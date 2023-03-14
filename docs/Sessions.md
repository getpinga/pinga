# Sessions

## Add in main start-xx-http.php file

```php
use App\PiSession;

$app['session'] = new PiSession();
$app['session']->start();
```

## Add in your controller

```php
use App\PiSession;

private $session;

public function __construct($app) {
    $this->session = $app['session'];
}
```

then

```php
// set a session variable
$this->session->set('username', 'johndoe');

// get a session variable
$username = $this->session->get('username');
```
