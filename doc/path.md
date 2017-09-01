# Path

Path allows to match PHP chain in order to get values.
It looks like (on purpose) Linux path syntax to be easy use.

## Path item

Path items are separated by slashes
```
/myObject/myMethod/myArrayKey
```
## Path aliasing

In a path items, array keys or object properties can be accessed the same way : 
```
/myObject/myProperty
/myArray/myKey
```
'get' can be omitted for getter method so can be the parenthesis if no parameter is required : 
```
/myObject/getSometing()
/myObject/something()
/myObject/something

/myObject/getSometing("param")
/myObject/something("param")
```
## Keyword

Keywords are prefixed by # and implement special behaviors.

Available keyword (there is only one for now):

* __\#any__: Get all values form an array or an object usable with foreach (such as class implemented from Iterator Interface)

# Path array

A path array is a PHP array gathering paths in order to produce structured arrays.

__Example :__
```
[
    'my/first/path',
    'my/second/path'
]
```
Result example : 
```
[
    'my value #1',
    'my value #2',
]
```

## Nested path array

Path array can be nested in order to produce structured result. 
Every array level contains pathes that will be resolved to produce a results in the __result scope__ defined by the array level.

```
[
    'my/first/path',
    'my/second/path' => [
        'continue/here',
        'continue/there'
    ]
    'my/first/path',
]
```
Result example :
```
[
    'Result 1',
    [
        'Result 2.1',
        'Result 2.2'
    ],
    'Result 3'
]
```
## Mecanisms

### Path id

Id are used to identify path in a path array.
It is located on start of the path and ends with a ':'

It can have 2 usage : 

* Usage 1 : It can be used to specify key in the result scope
* Usage 2 : It can be used in a __modifier__ to refer to a path element

Usage 1 example : 

```
[
    'lv-1:my/first/path',
    'lv-2:my/second/path' => [
        'lv-2-1:continue/here',
        'lv-2-2:continue/there'
    ]
    'lv-3:my/first/path',
]
```
Result example :
```
[
    'lv-1' => 'Result 1',
    'lv-2' => [
        'lv-2-1' => 'Result 2.1',
        'lv-2-2' => 'Result 2.2'
    ],
    'lv-3' => 'Result 3'
]
```

### Symbols

. : The element corresponding to the current path item
.. : The element corresponding to the previout path item


### Modifier

A path array can contain modifiers.
Modifiers are prefixed using "?" and allows to alter the path array behavior.

#### unique modifier

Preserve only unique value in the result scope.

#### transform modifier

Transform an element.

#### consider modifier

Allows to test if an element must be kept or not in the result scope.

#### keep-array

When the result scope contains only one (non keyed) value the result is the value an not an array.
In some case you may want to preserve the array anyway. ``keep-array`` allows it.

#### default-value

Allows to define default value when a path fail to resolve.

