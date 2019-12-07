Invoke
======

[![Build Status](https://travis-ci.org/dantleech/invoke.svg?branch=master)](https://travis-ci.org/dantleech/invoke)

Utility class to create new classes or invoke methods using named arguments.

PHP does not currently support [named
parameters](https://wiki.php.net/rfc/named_params), this utility provides a
convienient way to emulate them.

Installation
------------

Require with composer:

```bash
$ composer require dantleech/invoke
```

Usage
-----

Instantiate a new class:

```php
<?php

use DTL\Invoke\Invoke;

class Foobar
{
    public function __construct(string $arg1, string $arg2 = 'val1')
    {
    }
}

$foo = Invoke::new(Foobar::class, [
    'arg1' => 'value1'
]);
```

Invoke a method:

```php
<?php

use DTL\Invoke\Invoke;

class Foobar
{
    // ...

    public function one(string $two)
    {
    }
}

$foo = Invoke::new(Foobar::class, [
    'arg1' => 'value1'
]);

$bar= Invoke::method($foo, 'one', [
    'two' => 'bar'
]);
```

Contributing
------------

Pull requests are welcome. For major changes, please open an issue first to
discuss what you would like to change.

Please make sure to update tests as appropriate.

License
-------

[MIT](https://choosealicense.com/licenses/mit/)
