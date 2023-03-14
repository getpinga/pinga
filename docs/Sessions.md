# Sessions

## Add in main start-xx-http.php file

```
use App\PiSession;

$app['session'] = new PiSession();
$app['session']->start();
```

## Add in your controller

```
use App\PiSession;

private $session;

public function __construct($app) {
    $this->session = $app['session'];
}
```

then

```
// set a session variable
$this->session->set('username', 'johndoe');

// get a session variable
$username = $this->session->get('username');
```
