# Docs
* [Readme](../README.md)
* [Usage](USAGE.md)

# Installation

## Requirement
- [PDO Informix](https://pecl.php.net/package/PDO_INFORMIX) extension installed.


## Installation
Installation can be done via composer

    composer nfaiz/pdoifx


## Setup
Add properties below to `app/Config/Toolbar.php`;

```php
/**
 * -------------------------------------------------------------
 * Enable/Disable PdoIfx Event Collector
 * -------------------------------------------------------------
 *
 * @var boolean
 */
public bool $pdoIfxCollector = true;

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
public string $pdoIfxTitle = 'Informix';

/**
 * -------------------------------------------------------------
 * DbToolbar View
 * -------------------------------------------------------------
 * 
 * To override DbToolbar query Highlighter view.
 *
 * @var array
 */
public string $dbToolbarTpl = 'Nfaiz\PdoIfx\Views\queries.tpl';

/**
 * -------------------------------------------------------------
 * Disable DbToolbar query Highlighter
 * -------------------------------------------------------------
 * 
 * To disable DbToolbar query highlighter, change value to true
 *
 * @var boolean
 */
public bool $dbToolbarDisable = false;
```

Please refer [here](https://github.com/nfaiz/dbtoolbar#configuration) for more query styling configurations.
