![GitHub](https://img.shields.io/github/license/nfaiz/ci4-ifx)
![GitHub repo size](https://img.shields.io/github/repo-size/nfaiz/ci4-ifx?label=size)
![Hits](https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=nfaiz/ci4-ifx)

# CI4-Ifx

## Description
Informix wrapper for CodeIgniter 4 using PDO Informix extension.

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


> [!NOTE]
> This is a wrapper for INFORMIX. Don't expect it to work exactly like the CodeIgniter 4 database built-in function.
