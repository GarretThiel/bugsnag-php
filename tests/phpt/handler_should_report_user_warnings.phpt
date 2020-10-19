--TEST--
Bugsnag\Handler should report user warnings
--FILE--
<?php
$client = require __DIR__ . '/_prelude.php';

Bugsnag\Handler::register($client);

trigger_error('abc', E_USER_WARNING);

var_dump('Hello!');
?>
--EXPECTF--
Warning: abc in %s on line 6
string(6) "Hello!"
Guzzle request made (1 event)!
* Method: 'POST'
* URI: 'http://localhost/notify'
* Events:
    - abc
