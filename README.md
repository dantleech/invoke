Invoke
======

[![Build Status](https://travis-ci.org/dantleech/invoke.svg?branch=master)](https://travis-ci.org/dantleech/invoke)

Utility class to create new classes or invoke methods using named arguments.

PHP does not currently support [named
parameters](https://wiki.php.net/rfc/named_params), this utility provides a
convenient way to emulate them.

Installation
------------

Require with composer:

```bash
$ composer require dantleech/invoke
```

Why
---

Sometimes arguments may be sourced from arrays e.g. for "deserialization" or
instantiating configuration nodes).

Validating the existence of array keys, checking their types etc. is error
prone and time consuming.

By using `Invoke::new(MyObject::class, $array)` you can map the array keys
directly to the `__construct` parameters.

This library will, throw descriptive exceptions:

- If there are extra keys.
- If there are missing required keys (i.e. non-nullable values).
- If the types are wrong.

Performance
-----------

`Inoke::new(Clas::class, [])` is around 50x slower than `new Class()`, or
260,000 operations per second vs. ~13,000,000.

```
+--------------------------+---------+
| subject                  | mode    |
+--------------------------+---------+
| benchInvokeNewClass      | 3.720μs |
| benchInstantiateNewClass | 0.076μs |
+--------------------------+---------+
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

Alternatives
------------

[nikolaposa/cascader](https://github.com/nikolaposa/cascader)
Utility for creating objects in PHP from constructor parameters definitions.

Contributing
------------

Pull requests are welcome. For major changes, please open an issue first to
discuss what you would like to change.

Please make sure to update tests as appropriate.

License
-------

[MIT](https://choosealicense.com/licenses/mit/)
