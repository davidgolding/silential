# Silential

Silential is a minimalist microframework for PHP 7+. Start with core functions that provide a standard request/response cycle without forcing routing conventions or requiring a large collection of supporting libraries. Scale up as you need. Intended for small projects or narrow use cases.

## Philosophy

Minimalism and lightweight to support a high level of customization. Libraries and dependencies should be made as optional as possible, allowing for scalable architecture.

## Installation

Place the `silential` folder on your webserver. Done.

## Usage

In an `index.php` or similar file, run:

```php
<?php

require 'libraries/Core.php';

echo libraries\Controller::run();

?>
```

Then in a `controllers\AppController.php` file, capture a default request:

```php
<?php

namespace controllers;

class AppController {

    public static function index($args = []) {
        return $args['response']->render('Hello World');
    }
}

?>
```

## License
[MIT](https://choosealicense.com/licenses/mit/)