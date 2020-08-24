# Datamanager @HnrAzevedo

[![Maintainer](https://img.shields.io/badge/maintainer-@hnrazevedo-blue?style=flat-square)](https://github.com/hnrazevedo)
[![Latest Version](https://img.shields.io/github/v/tag/hnrazevedo/Datamanager?label=version&style=flat-square)](Release)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/quality/g/hnrazevedo/Datamanager?style=flat-square)](https://scrutinizer-ci.com/g/hnrazevedo/Datamanager/?branch=master)
[![Build Status](https://img.shields.io/scrutinizer/build/g/hnrazevedo/Datamanager?style=flat-square)](https://scrutinizer-ci.com/g/hnrazevedo/Datamanager/build-status/master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/hnrazevedo/Datamanager?style=flat-square)](https://packagist.org/packages/hnrazevedo/Datamanager)
[![Total Downloads](https://img.shields.io/packagist/dt/hnrazevedo/Datamanager?style=flat-square)](https://packagist.org/packages/hnrazevedo/Datamanager)

###### Datamanager is a simple persistence abstraction component in the database. Its author is not a professional in the development area, just someone from the Technology area who is improving his knowledge.

O Datamanager é um simples componente de abstração de persistência no banco de dados. Seu autor não é profissional da área de desenvolvimento, apenas alguem da área de Tecnologia que está aperfeiçoando seus conhecimentos.

### Highlights

- Easy to set up (Fácil de configurar)
- Total CRUD asbtration (Asbtração total do CRUD)
- Create safe models (Crie de modelos seguros)
- Composer ready (Pronto para o composer)

## Installation

Datamanager is available via Composer:

```bash 
"hnrazevedo/datamanager": "^1.0"
```

or run

```bash
composer require hnrazevedo/Datamanager
```

## Documentation

###### For details on how to use the Datamanager, see the sample folder with details in the component directory
Para mais detalhes sobre como usar o Datamanager, veja a pasta de exemplos com detalhes no diretório do componente

### Errors

#### In case of errors, Datamanager will throw a DatamanagerException, so it is necessary to import it into your class
Em casos de erros, o Datamanager disparara uma DatamanagerException, então é necessário importar a mesma em sua classe.

```php
use HnrAzevedo\Datamanager\DatamanagerException;
```

### Connection

##### To begin using the Datamanager, you need to connect to the database (MariaDB / MySql). For more connections [PDO connections manual on PHP.net](https://www.php.net/manual/pt_BR/pdo.drivers.php)
Para começar a usar o Datamanager precisamos de uma conexão com o seu banco de dados. Para ver as conexões possíveis acesse o [manual de conexões do PDO em PHP.net](https://www.php.net/manual/pt_BR/pdo.drivers.php)

```php
define("DATAMANAGER_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "charset" => "utf8",
    "port" => 3306,
    "username" => "root",
    "password" => "",
    "database" => "",
    "timezone" => "America/Sao_Paulo",
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING
    ],
    "dateformat" => "d/m/Y"
]);
```

### Your model

#### The Datamanager is based on an MVC structure. Soon to consume it is necessary to create the model of your table and inherit the Datamanager\Model.
O Datamanager é baseado em uma estrutura MVC. Logo para consumir é necessário criar o modelo de sua tabela e herdar o Datamanager\Model.

```php
namespace Model;

use HnrAzevedo\Datamanager\Model;

class User extends Model
{
    public function __construct()
    {
        /**
         * @param string Table name
         * @param string Primary key column
         */
        parent::create('user','id');
    }
}
```

## Methods

### Find
```php
use Model\User;
    
$entity = new User();

/* Find by primary key */
$user = $entity->find(1)->execute()->first()->toEntity();

/* Search only for columns defined in advance  */
$user = $entity->find(1)->only(['name','email'])->execute()->first();
$name = $user->name;
$email = $user->email;
/* OR */
$name = $entity->find()->only('name')->execute()->first()->name;

/* Search except for columns defined in advance  */
$user = $entity->find()->except(['name','email'])->execute()->first();
/* OR */
$user = $entity->find()->except('name')->execute()->first();

/* Limit example */
$users = $entity->find()->limit(5)->execute()->result();
/* Offset example */
$users = $entity->find()->limit(5)->offset(5)->execute()->result();

/* OrdeBy example */
$users = $entity->find()->orderBy('birth ASC')->execute()->result();
/* OR */
$users = $entity->find()->orderBy('birth','ASC')->execute()->result();

/* Between example */
$user = $entity->find()->between([
    'AND birth'=> [
        '01/01/1996',
        '31/12/1996'
        ]
    ])->execute()->first();

/* Condition AND is default */
$user = $entity->find()->between([
    'birth'=> [
        '01/01/1996',
        '31/12/1996'
        ]
    ])->execute()->first();

/* Clause IN */
$user = $entity->find()->where([
    'birth'=> [
        '01/01/1996',
        '31/12/1996'
        ]
    ])->execute()->first();


/* Where example */
$user->find()->where([
    ['name','=','Henri Azevedo'],
    'OR' => [
        'email','LIKE','otheremail@gmail.com'
        ]
])->execute();

/* Searches through all records and returns a result array */
$results = $entity->find()->execute()->result();

/* Searches for all records and returns an array of Model\User objects */
$results = $entity->find()->execute()->toEntity();
```

### Save
```php
$entity = new User();

$user = $entity->find()->execute()->first();

/* Change info to update */
$user->name = 'Other Name';
$user->email = 'otheremail@gmail.com';

/* Upload by primary key from the uploaded entity */
/* If the changed information is a primary key or a foreign key it will be ignored in the update */
/* NOTE: Must already have the Model returned from a query */
$user->save();
```
#### If there are no state changes to be saved, a DatamanagerException will be thrown.
Caso não haja mudanças de estado para serem salvas, será lançada uma DatamanagerException.

### Remove
```php
use Model\User;

$entity = new User();

/* Remove by cause *Where* */
$entity->remove()->where([
    ['name','=','Other Name'],
    'OR' => ['email','LIKE','otheremail@gmail.com']
])->execute();

/* Remove by primary key */
/* NOTE: Required to have already returned a query */
$entity->remove()->execute();
/* OR */
$entity->remove(true);
```

### Persist
```php
use Model\User;

$entity = new User();

/* Set new info for insert in database */
$entity->name = 'Henri Azevedo';
$entity->email = 'hnr.azevedo@gmail.com';
$entity->password = password_hash('123456' ,PASSWORD_DEFAULT);
$entity->birth = '28/09/1996';
$entity->register = date('Y-m-d H:i:s');

/* Insert entity in database */
$entity->persist();
```

### Count
```php
use Model\User;

$entity = new User();
$registers = $entity->find()->only('id')->execute()->count();
```

## Support

###### Security: If you discover any security related issues, please email hnrazevedo@gmail.com instead of using the issue tracker.

Se você descobrir algum problema relacionado à segurança, envie um e-mail para hnrazevedo@gmail.com em vez de usar o rastreador de problemas.

## Credits

- [Henri Azevedo](https://github.com/hnrazevedo) (Developer)
- [Robson V. Leite](https://github.com/robsonvleite) (Readme based on your datalayer design)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnrazevedo/Datamanager/blob/master/LICENSE.md) for more information.