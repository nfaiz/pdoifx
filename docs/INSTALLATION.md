# Installation and Configuration

## Installation
Installation an be done via composer

    composer nfaiz/pdoifx


## Configuration
* [Create PdoIfx Config file](#pdoifxphp)
* [Add PdoIfx Toolbar Collector](#toolbarphp)

### PdoIfx.php

Create `app/Config/PdoIfx.php` file by copying code below.

```php
<?php

namespace Config;

class PdoIfx extends \Nfaiz\PdoIfx\Config;
{
    /**
     * -------------------------------------------------------------
     * Query Log
     * -------------------------------------------------------------
     *
     * To enable or disable query logging.
     *
     * @var boolean
     */
    public $enableQueryLog = true;

    /**
     * -------------------------------------------------------------
     * Query Collector
     * -------------------------------------------------------------
     *
     * To enable or disable query collector.
     * $enableQueryLog must be set to true in order to use this.
     *
     * @var boolean
     */
    public $collect = true;

    /**
     * -------------------------------------------------------------
     * Tab Title
     * -------------------------------------------------------------
     *
     * Tab title display
     *
     * @var string
     */
    public $tabTitle = 'Queries';

    /**
     * -------------------------------------------------------------
     * Query Return Type
     * -------------------------------------------------------------
     *
     * object or array
     *
     * @var string
     */
    public $returnType = 'object';

    /**
     * -------------------------------------------------------------
     * Default Group
     * -------------------------------------------------------------
     *
     * Default DbGroup for connection if no DbGroup supplied
     *
     * @var string
     */
    public $defaultGroup = 'db_common';

    /**
     * -------------------------------------------------------------
     * DbGroup
     * -------------------------------------------------------------
     *
     * Can have multiple propery variable for multiple connection.
     *
     * @var array
     */
    public $db_common = [
        'host'     => 'host.domain.com',
        'server'   => 'ids_server',
        'database' => 'common_db',
        'username' => 'testuser',
        'password' => 'testpassword',
        'service'  => '9800',
        'protocol' => 'onsoctcp',
        'EnableScrollableCursors' => 1,
        'charset'   => 'utf8', # optional
        'collation' => 'utf8_unicode_ci', # optional
        'prefix'     => '', # optional
        'db_locale' => 'en_us.1252', # optional
        'client_locale' => 'en_us.1252', # optional
    ];

    /**
     * -------------------------------------------------------------
     * DbGroup
     * -------------------------------------------------------------
     *
     * Can have multiple propery variable for multiple connection.
     *
     * @var array
     */
    public $db_common2 = [
        'host'     => 'host.domain.com',
        'server'   => 'ids_server',
        'database' => 'common_db2',
        'username' => 'testuser2',
        'password' => 'testpassword2',
        'service'  => '9800',
        'protocol' => 'onsoctcp',
        'EnableScrollableCursors' => 1,
        'charset'   => 'utf8mb4', # optional
        'collation' => 'utf8mb4_general_ci', # optional
        'prefix'     => '', # optional
        'db_locale' => 'en_us.1252', # optional
        'client_locale' => 'en_us.1252', # optional
    ];
}
```

### Toolbar.php
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

and add properties below for sql highlighter styling. (Optional)

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

**$queryTheme**

* Assign stylesheet theme to `light` or `dark` mode. E.g replace `'default'` to `'atom-one-light'`
* Available stylesheets can be found using HighlightUtilities. See [highlighter-utilities](https://github.com/scrivo/highlight.php#highlighter-utilities) for more information


E.g To find available style sheets using  `\HighlightUtilities`

```php
// Get available stylesheets.
$availableStyleSheets = \HighlightUtilities\getAvailableStyleSheets();
d($availableStyleSheets);
```

**$queryMarginBottom**

* Assign value with integer type
* value is in pixel (`px`)

**$logger**

* Assign value to `true` to enable logger
* Need to set threshold to minimum `7` at `app/Config/Logger.php`
* Logs can be found at `ROOTPATH/writable/logs`


Usage Documentation can be found [here](USAGE.md#usage)
