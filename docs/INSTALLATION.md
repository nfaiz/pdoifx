# Installation

## Installation
Installation an be done via composer

    composer nfaiz/pdoifx


## Toolbar.php
Open `app/Config/Toolbar.php`

Add `\Nfaiz\PdoIfx\Collectors\Database::class` in **$collectors** property

```diff

public $collectors = [
    Timers::class,
    Database::class,
+   \Nfaiz\PdoIfx\Collectors\Database::class
    Logs::class,
    Views::class,
    // \CodeIgniter\Debug\Toolbar\Collectors\Cache::class,
    Files::class,
    Routes::class,
    Events::class,
];
```

and add properties below;

```php
/**
 * -------------------------------------------------------------
 * Enable/Disable PdoIfx Event Collector
 * -------------------------------------------------------------
 *
 * @var boolean
 */
public $ifxCollector = true;

/**
 * -------------------------------------------------------------
 * PDO Informix Tab Title
 * -------------------------------------------------------------
 *
 * Note: Use different title from other debug toolbar tab
 *
 * 
 * @var boolean
 */
public $ifxTitle = 'Informix';
```

and for query styling add properties below. (Optional)

```php
/**
 * -------------------------------------------------------------
 * Query Theme
 * -------------------------------------------------------------
 * 
 * Configuration for light and dark mode SQL syntax highlighter.
 *
 * @var array
 */
public $queryTheme = [
    'light' => 'default',
    'dark'  => 'dark'
];

/**
 * -------------------------------------------------------------
 * Bottom Margin Between Diplayed Query in Toolbar
 * -------------------------------------------------------------
 * 
 * Value in px
 * 
 * @var int
 */
public $queryMarginBottom = 4;

/**
 * -------------------------------------------------------------
 * Log Queries
 * -------------------------------------------------------------
 *
 * Need to set threshold to minimum 7 at app/Config/Logger.php
 *
 * @var boolean
 */
public $logger = false;

```

Refer [here](https://github.com/nfaiz/dbtoolbar#configuration) for query styling configurations.


Usage documentation can be found [here](USAGE.md#usage)
