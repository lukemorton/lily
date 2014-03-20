# Lily

A lightweight web application library for PHP 5.3+.

Lily provides your application with a common sense interface to HTTP. She also
provides routing, exception handling, dependency injection and more. This
project is inspired by the design of [ring][ring], especially in regards to the
use of higher order functions and middleware. 

Lil' Lily has very little to her weighing in at 1019LOC. She aims to be readable
in one sitting.

```php
<?php
require __DIR__.'/vendor/autoload.php';

$routes = [['GET', '/', 'Hello world']];
$handler = new Lily\Application\RoutedApplication(compact('routes'));
(new Lily\Adapter\HTTP)->run(compact('handler'));
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
        "drpheltright/lily": "~0.7.0"
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
[error-handling]: https://github.com/DrPheltRight/lily/wiki/003a-errors
[di]: https://github.com/DrPheltRight/lily/wiki/003b-di
[default-headers]: https://github.com/DrPheltRight/lily/wiki/003c-default-headers
[cookies]: https://github.com/DrPheltRight/lily/wiki/003d-cookies
[sessions]: https://github.com/DrPheltRight/lily/wiki/003e-sessions
[flash]: https://github.com/DrPheltRight/lily/wiki/003f-flash

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

## Development

All development should be done via [GitHub Issues][issues]. New features should
be suggested/planned there and can be identified by the `feature` tag. Also bugs
should be reported with the `bug` label.

I'd like some development help with new features. I've labelled the features
that people are welcome to hack at with `please help`. If a task isn't labelled
please just comment to ask if you want to help out.

**Features must be tested before pull requests opened.**

[issues]: https://github.com/DrPheltRight/lily/issues

## License

Copyright © 2014 Luke Morton

Distributed under MIT. See [LICENSE][license] distributed with Lily for more
information. TL;DR, Keep the license found at the top of each Lily file with
significant chunks of code you want to reuse.

[license]: https://github.com/DrPheltRight/lily/blob/develop/LICENSE
