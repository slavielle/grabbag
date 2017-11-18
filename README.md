# Grabbag - Grab objects with ease for PHP
[![Build Status](https://travis-ci.org/slavielle/grabbag.svg?branch=master)](https://travis-ci.org/slavielle/grabbag)
[![Coverage Status](https://coveralls.io/repos/github/slavielle/grabbag/badge.svg?branch=dev)](https://coveralls.io/github/slavielle/grabbag?branch=dev)
[![Maintainability](https://api.codeclimate.com/v1/badges/35360fdf935fc9804e3c/maintainability)](https://codeclimate.com/github/slavielle/grabbag/maintainability)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/slavielle/grabbag.svg?style=flat-square)](https://img.shields.io/packagist/v/slavielle/grabbag.svg)
[![license](https://img.shields.io/github/license/slavielle/grabbag.svg)](https://github.com/slavielle/grabbag/blob/master/LICENSE)

Grabbag is a PHP library that aims to gather and transform data from PHP arrays and objects using paths and queries.
Grabbag can reminds you of Xpath or JsonPath or again Symfony PropertyAccess or cakePHP Hash in a way, but Grabbag aims to offer a proper approach and features.

## Installation
```
$ composer require slavielle/grabbag
```

## Grabbing values

Grabbag allows to fetch values using path and collect them in an array
```php
$result = Grabbag::grab($input_data, [
    'name:companies/#0/staff/#0/list/#0/first_name',
    'age:companies/#0/staff/#0/list/#0/age',
]);
```
Result:
```
[
  'name' => 'Kate',
  'age' => '46',
]
```
[See example source here](https://github.com/slavielle/grabbag-playground/blob/master/examples/1-company/example-1.php)

You can write previous query this way too : 

```php
$result = Grabbag::grab($input_data, [
    'companies/#0/staff/#0/list/#0' => [
        'name:first_name',
        'age:age',
    ]
]);
```
[See example source here](https://github.com/slavielle/grabbag-playground/blob/master/examples/1-company/example-2.php)

## Grabbing array and object using %any

Grabbag can collect more than one simple value using ```%any``` keyword in path.
Let's consider the following example that looks like the previous one except for the ```%any``` keyword usage :
```php
$result = Grabbag::grab($input_data, [
    'companies/#0/staff/#0/list/%any' => [
        'name:first_name',
        'age:age',
    ]
]);
```
Result:
```php
[
  [
    'name' => 'Kate',
    'age' => '46',
  ],
  [
    'name' => 'Jack',
    'age' => '25',
  ],
]
```
[See example source here](https://github.com/slavielle/grabbag-playground/blob/master/examples/1-company/example-3.php)

## Structured result
Grabbag Queries allow to go a step further by gathering paths in order to produce structured results.
Lets take an example : 

```php 
$result = Grabbag::grab($input_data, [
    'companies/%any/' => [
        'company-name:name',
        'employee-list:staff/%any/list/%any' => [
            'name:first_name',
            'age:age',
        ],
    ],
]);
```
will produce a structured array such as : 
```php
[ 
  [
    'company-name' => 'Acme Corporation',
    'employee-list' => [
      [
        'name' => 'Kate',
        'age' => '46',
      ],
      [
        'name' => 'Jack',
        'age' => '25',
      ],
      [
        'name' => 'John',
        'age' => '34',
      ],
      [
        'name' => 'Barbara',
        'age' => '29',
      ],
    ],
  ],
  [
    'company-name' => 'Globex Corporation',
    'employee-list' => [
      [
        'name' => 'John',
        'age' => '32',
      ],
      [
        'name' => 'Barbara',
        'age' => '24',
      ],
      [
        'name' => 'Jack',
        'age' => '39',
      ],
      [
        'name' => 'Kate',
        'age' => '24',
      ],
    ],
  ],
]
```
[See example source here](https://github.com/slavielle/grabbag-playground/blob/master/examples/1-company/example-4.php)

## Want to know more about ?

Take a tour on the 
* [Examples](examples)
* [Documentation](doc)

## Field of use

Grabbag can be used in projects requiering manipulation, or transformation involving arrays or objects. 

A quick list for possible usage I have in mind are : 
* Producing json data for RESTful web-services.
* [JSON transformation](examples/3-json-friends-and-fruits)
* Producing variables for twig templates.
* Data export



