![GitHub](https://img.shields.io/github/license/nfaiz/ci4-ifx)
![GitHub repo size](https://img.shields.io/github/repo-size/nfaiz/ci4-ifx?label=size)
![Hits](https://hits.seeyoufarm.com/api/count/incr/badge.svg?url=nfaiz/ci4-ifx)

# CI4-Ifx
CodeIgniter 4 Informix Database Package

## Description
CodeIgniter 4 Informix Database Query Builder

## Requirement
* [CodeIgniter 4](https://github.com/codeigniter4/CodeIgniter4)
* [DbToolbar](https://github.com/nfaiz/dbtoolbar)

## Example Usage
```php

$builder = service('ifx', 'db_common'); // Using db_common for DbGroup

$result = $builder->table('users')
	->select('id, name')
	->where('age', '>', 18)
	->orderBy('id', 'desc')
	->limit(20)
	->getResult();

d($result);
```

## Screenshot

<img src="https://user-images.githubusercontent.com/1330109/129525581-9bb99cc5-4a34-495e-9274-5138a5346d22.png" alt="Debug">

## Docs
* [Installation and Configuration](docs/INSTALLATION.md)<br />
* [Usage](docs/USAGE.md)
