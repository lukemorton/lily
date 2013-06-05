# Lily

A lightweight web application library for PHP 5.3+.

Lily provides your application with a common sense interface to HTTP. Lily is
inspired by the design of [ring][1], especially in regards to the use of higher
order functions and middleware. She also provides routing if you need it.

Lil' Lily has very little to her weighing in at 761LOC. She aims to be readable
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

*This example uses PHP 5.4 as do all examples found in the [wiki][2]. Lily
supports 5.3+ though so you can backport examples.*

[2]: https://github.com/DrPheltRight/lily/wiki

## Installation

Installing Lily through [composer][3] is easy. Just create a `composer.json`
file in a new directory for your application:

```json
{
    "require": {
        "drpheltright/lily": "~0.3"
    }
}
```

Now run the following command in that directory:

```
curl -s https://getcomposer.org/installer | php && php composer.phar install
```

Done!

[3]: http://getcomposer.org/

## Documentation

 - [Start here][4]
 - [Basics][5]
 - [Routing][6]
 - [Middleware][7]
    - [Error handling][8]
    - [Dependency injection][]
    - [Default headers][]
    - [Cookies][]
    - [Sessions][]
    - [Flash][]
 - [Apache setup][]
 - [Nginx setup][]

 [4]: https://github.com/DrPheltRight/lily/wiki
 [5]: https://github.com/DrPheltRight/lily/wiki/Learning-the-basics
 [6]: https://github.com/DrPheltRight/lily/wiki/Routing-like-a-pro
 [7]: https://github.com/DrPheltRight/lily/wiki/Get-the-most-from-middleware
 [8]: https://github.com/DrPheltRight/lily/wiki/Error-handling

## Tests

[![Build Status](https://travis-ci.org/DrPheltRight/lily.png?branch=develop)][9]

To run the test suite, you need [composer][10] and it will handle the rest. Lily
unit tests are written with [PHPUnit][11].

```
php composer.phar install --dev
vendor/bin/phpunit
```

We try and keep coverage high and no feature is added without some kind of test.

[9]: https://travis-ci.org/DrPheltRight/lily
[10]: http://getcomposer.org/
[11]: https://github.com/sebastianbergmann/phpunit/

## Todo

 - Add session middleware with cookie support
 - Add flash middleware
 - Add accepts and lang parsing middleware
 - Add application testing adapter
 - Add file sending capabilities

## License

Copyright © 2013 Luke Morton

Distributed under MIT.
