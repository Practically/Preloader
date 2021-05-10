# Preloader

PHP preloading for PHP >=7.4. Preloading is a feature of php that will pre-compile php functions and classes to opcache. Thus, this becomes available in your
programs with out needing to require the files, which improves speed. To read more on php preloading you can see the [opcache.preloading
documentation](https://www.php.net/manual/en/opcache.preloading.php).

## Installation

The preferred method is with composer.

```bash
composer require practically/preloader
```

## Usage

After installation you need to create a preload executable.

```php
#!/usr/bin/env php
<?php

use Composer\Autoload\ClassLoader;
use Practically\Preloading\Preloader;

if (!function_exists('opcache_compile_file') || !ini_get('opcache.enable')) {
    echo 'Opcache is not available.';
    die(1);
}

if ('cli' === PHP_SAPI && !ini_get('opcache.enable_cli')) {
    echo 'Opcache is not enabled for CLI applications.';
    die(1);
}

/** @var ClassLoader */
$autoloader = require __DIR__ . '/vendor/autoload.php';
$preloader = new Preloader($autoloader);

/**
 * Set regex patterns of classes you want to exclude from preloading. In this
 * example any of the yii2 dev classes and the `Object` class that has been removed
 * and is no longer used
 */
$preloader->exclude('/^yii\\\composer/');
$preloader->exclude('/^yii\\\test/');
$preloader->exclude('/yii\\\base\\\Object/');

/**
 * A regex pattern of classes to preload this will get of the preloader and
 * preload all of the yii2 classes excluding the above and all of the app
 * classes
 */
$preloader->preload('/^yii/');
$preloader->preload('/^app/');

/**
 * Preload a directory of php files recucivly
 */
$preloader->preloadDirectory(__DIR__ . '/views');
```

The preloader uses the composer class map under the hood, so you will need to
generate the class map using composer optimised autoloader with the `-o` flag
when running composer.

```bash
composer install -o
```

Once all that is done you will need to configure you `php.ini` to use the
preload script to preload classes and files into the opcache.

```ini
;
; Ensure opcache is enable
;
opcache.enable=1
;
; You may need to ensure opcache is enable for cli scripts if needed
;
opcache.enable_cli=1
;
; Set the path to the preload script and setup the user if needed
;
opcache.preload=/path/to/preload.php
opcache.preload_user=user
```

## Contributing

### Getting set up

Clone the repo and run `composer install`.
Then start hacking!

### Testing

All new features of bug fixes must be tested. Testing is with phpunit and can
be run with the following command:

```bash
composer run-script test
```

### Coding Standards

This library uses [Practically](https://practically.io/) coding standards and `squizlabs/php_codesniffer`
for linting. There is a composer script for this:

```bash
composer run-script cs:check
```

### Pull Requests

Before you create a pull request with you changes, the pre-commit script must
pass. That can be run as follows:

```bash
composer run-script pre-commit
```

## Credits

This package is created and maintained by [Practically.io](https://practically.io/)
