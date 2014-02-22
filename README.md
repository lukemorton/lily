# Lily

A lightweight web application library for PHP 5.3+.

Lily provides your application with a common sense interface to HTTP. She also
provides routing, exception handling, dependency injection and more. This
project is inspired by the design of [ring][ring], especially in regards to the
use of higher order functions and middleware. 

Lil' Lily has very little to her weighing in at 904LOC. She aims to be readable
in one sitting.

```php
<?php
require __DIR__.'/vendor/autoload.php';

(new Lily\Adapter\HTTP)->run(
    new Lily\Application\RoutedApplication(
        [['GET', '/', 'Hello world']]));
?>
```

*This example uses PHP 5.4 as do all examples found in the [wiki][wiki]. Lily
supports 5.3+ though so you can backport examples.*

A slightly more complex example can be found in [`/example`][example].

[ring]: https://github.com/ring-clojure/ring
[wiki]: https://github.com/DrPheltRight/lily/wiki/000-overview
[example]: https://github.com/DrPheltRight/lily/blob/develop/example/

## Installation

Installing Lily through [composer][composer] is easy. Just create a `composer.json`
file in a new directory for your application:

```json
{
    "require": {
        "drpheltright/lily": "~0.4"
    }
}
```

Now run the following command in that directory:

```
curl -s https://getcomposer.org/installer | php && php composer.phar install
```

Done!

[composer]: http://getcomposer.org/

## Documentation

 - [Start here][start-here]
 - [Basics][basics]
 - [Routing][routing]
 - [Middleware][middleware]
    - [Error handling][error-handling]
    - [Dependency injection][di]
    - [Default headers][default-headers]
    - [Cookies][cookies]
    - [Sessions][sessions]
    - [Flash][flash]

 [start-here]: https://github.com/DrPheltRight/lily/wiki/000-overview
 [basics]: https://github.com/DrPheltRight/lily/wiki/001-basics
 [routing]: https://github.com/DrPheltRight/lily/wiki/002-routing
 [middleware]: https://github.com/DrPheltRight/lily/wiki/003-middleware
 [error-handling]: https://github.com/DrPheltRight/lily/wiki/errors
 [di]: https://github.com/DrPheltRight/lily/wiki/di
 [default-headers]: https://github.com/DrPheltRight/lily/wiki/default-headers
 [cookies]: https://github.com/DrPheltRight/lily/wiki/cookies
 [sessions]: https://github.com/DrPheltRight/lily/wiki/sessions
 [flash]: https://github.com/DrPheltRight/lily/wiki/flash-messages

## Tests

[![Build Status](https://travis-ci.org/DrPheltRight/lily.png?branch=develop)][travis]
[![Coverage Status](https://coveralls.io/repos/DrPheltRight/lily/badge.png?branch=develop)][coveralls]

To run the test suite, you need [composer][composer] and it will handle the rest. Lily
unit tests are written with [PHPUnit][phpunit].

```
php composer.phar install --dev
vendor/bin/phpunit
```

We try and keep coverage high and no feature is added without some kind of test.

[travis]: https://travis-ci.org/DrPheltRight/lily
[coveralls]: https://coveralls.io/r/DrPheltRight/lily?branch=develop
[phpunit]: https://github.com/sebastianbergmann/phpunit/

## Todo

 - Add accepts and lang parsing middleware
 - Add file sending capabilities
 - Get coverage to 100% somehow

## License

Copyright © 2014 Luke Morton

Distributed under MIT.
