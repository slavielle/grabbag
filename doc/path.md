# Path

Grabbag Path looks like (on purpose) Linux path syntax to be easy use.
Path allows to match PHP chain in order to get values.


## Path items

Path items are separated by slashes.
Let's see one simple example in raw PHP and its equivalent Grabbag path.

PHP
```php
myObject->myMethod()['myArrayKey']
```
Grabbag path : 
```
/myObject/myMethod/myArrayKey
```

## Path syntax

In a path, array keys or object properties can be accessed the same way : 
```
/myObject/myProperty
/myArray/myKey
```
No need to use distinct syntax as in raw PHP :
```php
$myObject->myProperty
$myArray['myKey']
```
'get' can be omitted from getter methods name so can be the parenthesis if no parameter is required.

Following paths are equivalent. 

```
/myObject/getSometing()
/myObject/something()
/myObject/something
```
Method items can only have one parameter provided for now : Method items aims to access getter method. one parameter is all we need in that case.  

Following paths are equivalent. 
```
/myObject/getSometing("param")
/myObject/something("param")
```

## Simple query

A query is a PHP array gathering paths in order to produce structured arrays.

**Example :**
```php
[
    "my/first/path",
    "my/second/path"
]
```
**Example result  :** 
```php
['my value #1', 'my value #2',]
```
In query, paths are often prefixed with id (See Path ids) allowing to produce a keyed value in the result.

## Path ids

Path ids are used to identify a path in a query and are located on start of the path and ends with a ':'

It can have 2 usage : 

* Usage 1 : It can be used to specify key in the result scope
* Usage 2 : It can be used in a __modifier__ to refer to a path value. 
If you want to avoid the key to be used in result scope you must prefix it with "~".

Usage 1 example : 

```php
[
    "lv-1:my/first/path",
    "~lv-2:my/second/path",
    "lv-3:my/first/path"
]
```
Result example :
```php
[
    "lv-1" => "Result 1",
    "Result 2",
    "lv-3" => "Result 3"
]
```

## Multiple level Query

### Embedded path arrays

In a query, Path arrays can be embedded in order to produce structured results. 
Each path array contains paths that will be resolved to produce results in the 
related result scope.

Usage 1 example : 

```php
[
    "my-key-A:my/first/path",
    "my-key-B:my/second/path" => [
        "my-key-AA:continue/here",
        "my-key-AB:continue/there"
    ]
    "my-key-C:my/first/path",
]
```

### Result scope
Result scope is an important Grabbag concepts.

Let consider the following path : 
```
my/path/#any/continues/#any
```
with the two #any correponding to iterable objects, and lets imagine last #any gets a string value.

The result would be something like this : 
```php
[
    "My string 1-1",
    "My string 1-2",
    "My string 2-1",
    "My string 2-2"
]
```
If we now split this path in a query containing 2 embedded path array like this :
 
```php
[
    "my/path/#any" => 
    [
        "continues/#any"
    ]
]
```

We request same objects pretty the same way, but we added a embedded path array having its own result scope. The whole result will now be someting like this :


```php
[
    [
        "My string 1-1",
        "My string 1-2"
    ],
    [
        "My string 2-1",
        "My string 2-2"
    ]
]
```


## Symbols

* . : The element corresponding to the current path item
* .. : The element corresponding to the previout path item

## Keywords

Keywords are path items prefixed by "#" that implements special behaviors.

* [\#any](path-keywords/any-keyword.md)
* [\#key](path-keywords/key-keyword.md)

## Modifiers

In a query, path arrays can contain modifiers.
Modifiers are prefixed using "?" and allows to alter the path array behavior.

* [?unique](query-modifiers/unique-modifier.md)
* [?transform](query-modifiers/transform-modifier.md)
* [?consider](query-modifiers/consider-modifier.md)
* [?keep-array](query-modifiers/keep-array-modifier.md)
* [?default-value](query-modifiers/default-value-modifier.md)
* [?exception-enabled](query-modifiers/exception-enabled-modifier.md)
* [?debug](query-modifiers/debug-modifier.md)




