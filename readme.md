
A Bitly API Wrapper for Lumen and Laravel (supports OAuth endpoints). It should support all basic endpoints that are found/available via http://dev.bitly.com/api.html.

======
Install:
======

  ```
  "javiertelioz/bitlylumen": "dev"
  ```

### Lumen:

After	 updating composer add the following lines to register provider in `bootstrap/app.php`

  ```
  $app->register('Javiertelioz\BitlyLumen\BitlyLumenServiceProvider');
  ```

======
Using:
======

1. Simply download the bitly.php file and include it in your project directory

2. Make sure to include the file whenever you want to access the bitly api functionality... include_once('bitly.php');

3. Set up an associative array with the required parameters for the endpoint you want to access.

4. Make the Bitly::get or Bitly::post call, passing in the endpoint and the parameters as defined via the bit.ly API documentation ( http://dev.bitly.com/api.html ).

=============
Examples:
=============

```php

$params = array();
$params['longUrl'] = 'http://google.com';
$params['domain'] = 'domain.com';
$results = Bitly::get('shorten', $params);
var_dump($results);
```

```php
$params = array();
$params['url'] = 'http://google.com';
$results = Bitly::get('link/lookup', $params);
var_dump($results);
```

a slightly more complex example with complex params (simply pass a third param of true when dealing with complex params):

```php
$params = array();
$params['hash'] = array('dYhyia','dYhyia','abc123');
$results = Bitly::get('expand', $params, true);
var_dump($results);
```

You can find more detailed examples in the test.php file within this repo.

=============
SPECIAL NOTE:
=============

To use the new OAuth endpoints, you must first obtain an access token for a user. You do this by passing the user off to bit.ly to approve your apps access to their account...and then you use the return code along with the bitly_oauth_access_token method to obtain the actual bitly access token:

1. Present the user with a link as such:

https://bit.ly/oauth/authorize?client_id=YOUR_BITLY_CLIENT_ID&redirect_uri=THE_URL_YOU_WANT_BITLY_TO_REDIRECT_TO_WHEN_APP_IS_APPROVED_BY_USER

2. a code ($_REQUEST['code']) will be supplied as a param to the url Bit.ly redirects to. So you can then execute:

```php
$results = bitly_oauth_access_token($_REQUEST['code'],
  'THE_URL_YOU_WANT_BITLY_TO_REDIRECT_TO_WHEN_APP_IS_APPROVED_BY_USER',
  'YOUR_BITLY_APP_CLIENT_ID',
  'YOUR_BITLY_APP_CLIENT_SECRET');
```

3. If everything goes correctly, you should now have a $results['access_token'] value that you can use with the oauth requests for that user.

=======
CONTACT:
=======

As always, if you've got any questions, comments, or concerns about
anything you find here, please feel free to drop me an email at jtelio118@gmail.com or find me on Twitter @jtelio