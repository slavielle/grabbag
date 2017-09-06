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
'get' can be omitted for getter methods so can be the parenthesis if no parameter is required.
Following paths are equivalent. 

```
/myObject/getSometing()
/myObject/something()
/myObject/something
```
```
/myObject/#each>getSometing("param")
/myObject/param>something("param")
```

Method items can only have one parameter provided : Method items aims to access getter method. one parameter is all we need in that case.  

## Keywords

Keywords are path items prefixed by "#" and implement special behaviors. there is only one keyword available for now Available keyword.

### #any
Get any values form an array or an object usable with foreach (such as array or instance of class implemented from Iterator Interface)

__Example :__
```
my/path/with/#any
```
Result example : 
```php
["value 1", "value 2", "value 3"]
```
## Path arrays

A path array is a PHP array gathering paths in order to produce structured arrays.

__Example :__
```php
[
    "my/first/path",
    "my/second/path"
]
```
Result example : 
```php
['my value #1', 'my value #2',]
```
In path array, paths are often prefixed with id (See Path ids) allowing to produce a keyed value in the result scope.

## Path ids

Id are used to identify a path in a path array and are located on start of the path and ends with a ':'

It can have 2 usage : 

* Usage 1 : It can be used to specify key in the result array
* Usage 2 : It can be used in a __modifier__ to refer to a path element

Usage 1 example : 

```php
[
    "lv-1:my/first/path",
    "lv-2:my/second/path",
    "lv-3:my/first/path"
]
```
Result example :
```php
[
    "lv-1" => "Result 1",
    "lv-2" => "Result 2",
    "lv-3" => "Result 3"
]
```

## Embedded path arrays and result scope

Path arrays can be embedded in order to produce structured results. 
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
If we now split this path in 2 embedded path array like this :
 
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


## Modifiers

A path array can contain modifiers.
Modifiers are prefixed using "?" and allows to alter the path array behavior.

#### the "unique" modifier

Preserve only unique value in the result scope.

#### the "transform" modifier

Allows to transform a result value using a callback function.

#### the "consider" modifier

Allows to test if an element must be kept or not in the result scope.

#### the "keep-array" modifier

When the result scope contains only one (non keyed) value the result is the value itself an not an array.
In some case you may want to preserve the array anyway. ``keep-array`` allows it.

#### the "default-value" modifier

Allows to define default value when a path fail to resolve.

#### the "exception-enabled" modifier

Allows all exception to be thrown. Useful for debug purpose.

#### the "debug" modifier

provide debug information on element

