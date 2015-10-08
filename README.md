# laravel5-less
less compiling for laravel5 based on oyejorge/less.php

## Examples
```php
Less::modifyVars(['color' => 'pink'])->parse('a {color:@color;}')->compile('filename');
```

