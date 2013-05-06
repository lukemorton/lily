# Lily

A lightweight web application library for PHP.

Lily provides your application with a common sense interface to HTTP. Lily is
inspired by the design of [ring][1], especially in regards to the use of higher
order functions and middleware. She also provides routing if you need it.

Lil' Lily has very little to her weighing in at 534LOC. She aims to be readable
in one sitting.

[1]: https://github.com/ring-clojure/ring

```php
<?php
require __DIR__.'/vendor/autoload.php';

(new Lily\Adapter\HTTP)->run(
    new Lily\Application\RoutedApplication(
        [['GET', '/', 'Hello world']]));
?>
```

*This example uses PHP 5.4, Lily supports PHP 5.3+. The same goes for all
examples found in the [wiki][9].*

[9]: https://github.com/DrPheltRight/lily/wiki

## Installation

Installing Lily through [composer][2] is easy. Just create a `composer.json`
file in a new directory for your application:

```json
{
    "require": {
        "drpheltright/lily": "~0.3"
    },

    "minimum-stability": "dev"
}
```

Now run the following command in that directory:

```
curl -s https://getcomposer.org/installer | php && php composer.phar install
```

Done!

[2]: http://getcomposer.org/

## Documentation

 - [Start here][10]
 - [Basics][3]
 - [Routing][4]
 - [Middleware][8]
    - [Error handling][11]

 [3]: https://github.com/DrPheltRight/lily/wiki/Learning-the-basics
 [4]: https://github.com/DrPheltRight/lily/wiki/Routing-like-a-pro
 [8]: https://github.com/DrPheltRight/lily/wiki/Get-the-most-from-middleware
 [10]: https://github.com/DrPheltRight/lily/wiki
 [11]: https://github.com/DrPheltRight/lily/wiki/Error-handling

## Tests

[![Build Status](https://travis-ci.org/DrPheltRight/lily.png?branch=develop)][5]

To run the test suite, you need [composer][6] and it will handle the rest. Lily
unit tests are written with [PHPUnit][7].

```
php composer.phar install --dev
vendor/bin/phpunit
```

We try and keep coverage high and no feature is added without some kind of test.

[5]: https://travis-ci.org/DrPheltRight/lily
[6]: http://getcomposer.org/
[7]: https://github.com/sebastianbergmann/phpunit/

## License

Copyright © 2013 Luke Morton

Distributed under MIT.
