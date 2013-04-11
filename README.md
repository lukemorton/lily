# Lily

A lightweight web application library for PHP. Lily provides
your application with the tools to interface with HTTP in a
modular way. Lily is inspired by [ring][1].

Lil' Lily has very little to her weighing in at 414LOC. She
aims to be readable in one sitting.

[1]: https://github.com/ring-clojure/ring

```php
<?php
require __DIR__.'/vendor/autoload.php';

(new Lily\Adapter\HTTP)->run(
    new Lily\Util\RoutedApplication(
        [['GET', '/', 'Hello world']]));
?>
```

*This example uses PHP 5.4, Lily supports PHP 5.3+.*

## License

Copyright © 2013 Luke Morton

Distributed under MIT.
