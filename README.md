# Grabbag - Grab objects with ease for PHP
[![Build Status](https://travis-ci.org/slavielle/grabbag.svg?branch=master)](https://travis-ci.org/slavielle/grabbag)
[![Coverage Status](https://coveralls.io/repos/github/slavielle/grabbag/badge.svg?branch=dev)](https://coveralls.io/github/slavielle/grabbag?branch=dev)
[![Maintainability](https://api.codeclimate.com/v1/badges/35360fdf935fc9804e3c/maintainability)](https://codeclimate.com/github/slavielle/grabbag/maintainability)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/slavielle/grabbag.svg?style=flat-square)](https://img.shields.io/packagist/v/slavielle/grabbag.svg)
[![license](https://img.shields.io/github/license/slavielle/grabbag.svg)](https://github.com/slavielle/grabbag/blob/master/LICENSE)

## Installation
```
$ composer require slavielle/grabbag
```

Grabbag is a PHP library that aims to provide a simple secure way to access PHP objects/arrays chains using path-like expressions.

## Features :
* Compact path like syntax using uniform syntax for accessing object properties, getters and array keys
* Prevent exceptions while resolving path and provide a default value when a path cannot be resolved
* Multiple values result using %any
* Structured result using query

## What is it all about ... a first example

### Using Raw PHP

When you develop - for instance - on top of PHP object-based frameworks or CMS, you often have to use long expressions to access objects/arrays chains.
Lets take an example from Drupal 8 : Getting the image URL from a node using entity reference field pointing to a media entity would give something like this :

```php
$result = $node->get('field_media_image')->first()->get('entity')->getTarget()->getValue()->get('field_image')->entity->getFileUri()
echo $result;
```

### Using Grabbag

Using Grabbag allows to get access to objects/arrays chains using a path expression

```php
$result = Grabbag::grab($node, 'get("field_media_image")/first/get("entity")/target/value/get("field_image")/entity/fileUri');
echo $result;
```

### Both approach comparision 
* The Raw PHP approach is not implicitly secure : Some of the methods/properties along the expression can return or be NULL in some case and then cause an exception. If you really want to secure expression, you would have to test some of the values before accessing them. That's a really boring point developer often have to deal with : it's repetitive, unappealing, can induce bugs and makes code less readable. 
* Grabbag approach is implicitly secure. If it's not possible to walk along the objects/arrays chain, result will be NULL by default or set to a default-value to be specified.

## Multiple values result using %any

Grabbag can collect more than one simple value using ```%any``` keyword in path.
Let's consider the following example that looks like the previous one except for the ```%any``` keyword usage :
```php
$result = Grabbag::grab($node, 'get("field_media_image")/%any/get("entity")/target/value/get("field_image")/entity/fileUri');
var_dump($result);
```
if the value corresponding to ```%any``` in the path can be used with ```foreach``` (if it's an array or a [traversable object](http://php.net/manual/en/class.traversable.php)), 
```%any``` will resolve the path considering each values and will produce a multi-valued result.

For instance if
```php
$node->get('field_media_image') 
```
contains 4 items, then the result of the former example will be an array looking like this : 
```php
[
    "my/image/1.jpg", 
    "my/image/2.jpg", 
    "my/image/3.jpg", 
    "my/image/4.jpg"
]
```
## Structured result using Grabbag queries
Grabbag Queries allow to go a step further by gathering paths in order to produce structured results.
Lets take an example : 

```php 

$result = Grabbag::grab($node, [
    'content-title:get("title").value',
    'images:get("field_media_image")/%any/get("entity")/target/value/get("field_image")' => [
        'uri:entity/fileUri',
        'alt:alt'
    ]
 ]);
var_dump($result);
```
will produce a structured array such as : 
```php
[
    'content-title' => 'My node title', 
    'images' => [
        [
            'uri' => "my/image/1.jpg",
            'alt' => "My image 1 alt"
        ],
        [
            'uri' => "my/image/2.jpg",
            'alt' => "My image 2 alt"
        ],
                [
            'uri' => "my/image/3.jpg",
            'alt' => "My image 3 alt"
        ],
    ]
]
```

## Want to know more about ?

Take a tour on the 
* [Examples](examples)
* [Documentation](doc)

## Field of use

Grabbag can be used in most of PHP project.

You can use it simply to secure access to objects/array chain or in a more complex manner to produce structured results.

Grabbag is particularly useful for projects built on top of object oriented frameworks/CMS such as Drupal 8, Symfony, 
Laravel and so on, and can be used everywhere you would have to manipulate values from PHP objects/arrays.

A quick list for possible usage I have in mind are : 
* Producing json data for RESTful web-services.
* [JSON transformation](examples/3-json-friends-and-fruits)
* Producing variables for twig templates.
* Data export



