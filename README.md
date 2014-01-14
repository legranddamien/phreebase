Phreebase, a simple but thorough PHP client for Freebase API
================================

Phreebase provides a nice API for all the features on [Google's Freebase API](https://developers.google.com/freebase/).

Requirements
------------

Phreebase works with PHP 5.4.0 or later.

You really shoud be using PHP 5.4.0 or later, but if you're not using it just yet, feel free to fork the code and replace array notation from `[]` to `array()` and it shoud work.

Installation
------------

The recommended and covered way of installation is through [Composer](http://getcomposer.org/).

To install Phreebase using composer you need to:

1) Make sure you already have composer.phar on your project root folder. If not run, go to you project root folder and run:

	curl -s http://getcomposer.org/installer | php

2) Create a composer.json file in your project root folder:
  
```json
{
  	"require": {
      	"brunomvsouza/phreebase": "dev-master"
  	}
}
```

3) Finally install Phreebase through Composer:
  
	php composer.phar install

Usage
-----

Full introduction to the Phreebase API.

At first you need to require composer's autoloader:

```php
require_once '/path/to/vendor/autoload.php';
```

Instanciate Phreebase class giving the your Google's Freebase API Key ([more info on how to get an API Key](https://developers.google.com/freebase/v1/getting-started#api-keys)):

```php
$phreebase = new Phreebase('__YOUR_FANCY_API_KEY__');
```

Make searches through plain strings with the ``search()`` method:

```php
// Full parameters list here https://developers.google.com/freebase/v1/search
$phreebase->search([
    'query' => 'Tropa de Elite',
    'filter' => '(any type:/people/person)'
]);
```

Make searches through [MQL Queries](https://developers.google.com/freebase/v1/mql-overview) with the ``mqlRead()`` method:

```php
// Full parameters list here https://developers.google.com/freebase/v1/mqlread
$phreebase->mqlRead([
    [
        'id' => null,
        'name' => null,
        'type' => '/astronomy/planet'
    ]
]);
```

Write on Freebase through [MQL Queries](https://developers.google.com/freebase/v1/mql-overview) with the ``mqlWrite()`` method:

```php
// To write on Freebase you need to ask for additional quota on
// https://developers.google.com/freebase/v1/mql-overview#mqlwrite-overview
// Full parameters list here https://developers.google.com/freebase/v1/mqlwrite
$phreebase->mqlWrite($yourMqlWriteArray);
```

Get a topic with the ``topic()`` method:

```php
// Full parameters list here https://developers.google.com/freebase/v1/topic
$phreebase->topic('/m/0463dr7', [
    'lang' => 'pt'
]);
```

More Information
----------------

I told it was simple. :) There is no more information to give (I think). If you have any questions, the code is your friend.

If the code don't give you the answer feel free to ask through an issue.


License
-------

Phreebase is licensed under the [MIT license](https://github.com/brunomvsouza/phreebase/blob/master/LICENSE).
