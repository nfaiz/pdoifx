![GitHub](https://img.shields.io/github/license/nfaiz/ci4-ifx)
![GitHub repo size](https://img.shields.io/github/repo-size/nfaiz/ci4-ifx?label=size)
![Hits](https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=nfaiz/ci4-ifx)

# CI4-Ifx
CodeIgniter 4 Informix Database Package

## Description
CodeIgniter 4 Informix Database Query Builder Using PDO.


## Example Usage
```php

$builder = ifx_connect();

$result = $builder->table('users')
    ->select('id, name')
    ->where('age', '>', 18)
    ->orderBy('id', 'desc')
    ->limit(2)
    ->getResult();

d($result);
```

## Screenshot

<img src="https://user-images.githubusercontent.com/1330109/129663982-da6196c4-92c9-4731-a3e1-005881784efe.png" alt="Debug">

## Docs
* [Installation](docs/INSTALLATION.md)
* [Usage](docs/USAGE.md)
