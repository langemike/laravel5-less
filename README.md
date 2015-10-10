# LESS support for Laravel 5.x without Node.js
Use LESS with your Laravel installation. Uses [oyejorge/less.php](http://lessphp.gpeasy.com/) instead of [leafo/lessphp](http://leafo.net/lessphp/) which is a more actively developed port of the official LESS processor. 

## Features
- Can modify LESS variables on-the-fly
- Can parse custom CSS/LESS and append it to the resulting file
- Works with Twitter Bootstrap v3.3.5 (thanks to oyejorge/less.php)
- Caching support

## Installation

First, pull in the package through Composer.

```js
"require": {
    "langemike/laravel5-less": "~1.0"
}
```

And then, if using Laravel 5, include the service provider within `config/app.php`.

```php
'providers' => [
    'Langemike\Laravel5Less\LessServiceProvider'
];
```

In the aliases section, add:

```php
'aliases' => [
    'Less' => 'Langemike\Laravel5Less\LessFacade'
];
```

## Configuration
In order to work with the configuration file, you're best off publishing a copy
with Artisan:

````
$ php artisan vendor:publish
````
This will create a config file 'less.php' in your config directory.

### Settings

You can specify your configuration through 3 options: `.env`, `config.php` file and through `$options` parameter.

Your .env configuration will be used by default, it will be overridden by it's config.php settings, but the $options parameter will have the highest preference.

### Recompilation
Additionally you can (and probably should) have different configurations for development 
and production.  Specifically, you probably don't want to be generating css files on
your production server, since it will slow down your site.

- change -- Check if LESS file(s) are modified. If it does, recompile CSS
- never -- Don't check, don't recompile.
- always -- Always rewrite CSS


## Usage

Within your models or controllers, you can perform modification to the outputted CSS. Here are some examples:
before you perform a redirect...

```php
public function recompileCSS()
{
    Less::modifyVars(['@body-bg' => 'pink'])->recompile('filename');

    return Redirect::back();
}
```

Within your view you can use the `Less::url()` function to link to your generated CSS

```html
	<link href="{!! Less::url('filename') !!}" rel="stylesheet" />
```

Passing `true` as the second parameter to `Less::url()` will auto-detect, based on your configuration, if recompilation is needed and will do so accordingly. 

## Credits
This project is inspired by [Less4Laravel](https://github.com/jtgrimes/less4laravel).
Without the hard work of [oyejorge/less.php](http://lessphp.gpeasy.com/) this project wouldn't be possible.
