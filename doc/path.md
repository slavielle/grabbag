# Path

Path allows to match PHP chain in order to get values.
It looks like (on purpose) Linux path syntax to be easy use.

## Path items

Path items are separated by slashes
```
/myObject/myMethod/myArrayKey
```
## Path aliasing

In a path, array keys or object properties can be accessed the same way : 
```
/myObject/myProperty
/myArray/myKey
```
'get' can be omitted for getter methods so can be the parenthesis if no parameter is required : 

Equivalent paths:
```
/myObject/getSometing()
/myObject/something()
/myObject/something
```
```
/myObject/getSometing("param")
/myObject/something("param")
```
## Keywords

Keywords are prefixed by "#" and implement special behaviors.

Available keyword (there is only one for now):

* __\#any__: Get all values form an array or an object usable with foreach (such as class implemented from Iterator Interface)

__Example :__
```
my/path/with/#any
```
Result example : 
```
["value 1", "value 2", "value 3"]
```
# Path arrays

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
['my value #1', 'my value #2',]
```

## Nested path arrays

Path arrays can be nested in order to produce structured result. 
Every array level contains paths that will be resolved to produce results in the 
Path array __result scope__.

```
[ // First result scope
    'my/first/path',
    'my/second/path' => [ // Second (nested) result scope
        'continues/here',
        'continues/there'
    ]
    'my/first/path',
]
```
Result example :
```
[ //Results from first result scope
    'Result 1',
    [ //Results from second result scope
        'Result 2.1',
        'Result 2.2'
    ],
    'Result 3'
]
```
## Mecanisms

### Path ids

Id are used to identify a path in a path array and is located on start of the path and ends with a ':'

It can have 2 usage : 

* Usage 1 : It can be used to specify key in the result array
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

* . : The element corresponding to the current path item
* .. : The element corresponding to the previout path item


### Modifiers

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

