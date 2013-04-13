# Lily

A lightweight web application library for PHP.

Lily provides your application with a common sense interface
to HTTP. Lily is inspired by the design of [ring][1],
especially in regards to the use of higher order functions and
middleware. She also provides routing if you need it.

Lil' Lily has very little to her weighing in at 414LOC. She
aims to be readable in one sitting.

[1]: https://github.com/ring-clojure/ring

```php
<?php
require __DIR__.'/vendor/autoload.php';

(new Lily\Adapter\HTTP)->run(
    new Lily\Application\RoutedApplication(
        [['GET', '/', 'Hello world']]));
?>
```

*This example uses PHP 5.4, Lily supports PHP 5.3+.*

## Installation

Installing Lily through [composer][1] is easy. Just create a
`composer.json` file in a new directory for your application:

```json
{
    "require": {
        "drpheltright/lily": "~0.1"
    }
}
```

Now run the following command in that directory:

```
curl -s https://getcomposer.org/installer | php && php composer.phar install
```

Done!

[1]: http://getcomposer.org/

## Tests

[![Build Status](https://travis-ci.org/DrPheltRight/lily.png?branch=develop)](https://travis-ci.org/DrPheltRight/lily)

To run the test suite, you need [composer][1] and it will
handle the rest. Lily unit tests are written with [PHPUnit][2].

```
php composer.phar install --dev
vendor/bin/phpunit
```

[1]: http://getcomposer.org/
[2]: https://github.com/sebastianbergmann/phpunit/

## License

Copyright © 2013 Luke Morton

Distributed under MIT.
