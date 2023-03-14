# Sessions

## Add in main start-xx-http.php file

```
use App\PiSession;

$session = new PiSession();
$session->start();

// set a session variable
$session->set('username', 'johndoe');

// get a session variable
$username = $session->get('username');
```
