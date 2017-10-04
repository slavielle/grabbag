# Path & query principles

* [Path principle](#path-principle)
    * [Path items](#path-items)
    * [Path items syntax](#path-items-syntax)
* [Query, a quick overview](#query-a-quick-overview)
    * [path-arrays](#path-arrays)
* [Path ids](#path-ids)
    * [Usage](#usage)
    * [Explicit or internal id](#explicit-or-internal-id)
    * [Ids and modifiers](#ids-and-modifiers)     
* [Query, let's go further](#query-lets-go-further)
    * [Embedded path-arrays](#embedded-path-arrays)
    * [Result scope](#result-scope)
    * [Single value path-array](#single-value-path-array)
* [Symbols](#symbols)
* [Keywords](#keywords)
* [Modifiers](#modifiers)


## Path principle

Grabbag offer an alternative to PHP chain to get value from PHP elements (objects or arrays).
It principle is to use a path that acts similarly to PHP chain but with some benefits.

for example, a PHP chain such as : 
```php
$element->getThat()->is['a']['path'];
```

```php
$result = Grabbag::grab($element, 'that/is/a/path');
```

Grabbag Path are directly [inspired from Linux path syntax](faq.md#why-choosing-linux-path-like-syntax). That was made on purpose to look familiar for developers and make Grabbag basic usage as easiest as possible.


### Path items

Path items are separated by slashes (so are the directories in linux path) an refer to a PHP element (objet, array, method or variable).

Array keys or object properties can be accessed the same way : 
```
/myObject/myProperty
/myArray/myKey
```
No need to use distinct syntax as in raw PHP :
```php
$myObject->myProperty; // Accessing an object's public property.
$myArray['myKey'];     // Accessing an array keyed item.
```
When path item refers an object getter method, 'get' can be omitted so can be the parenthesis if no parameter is required.

Following paths are equivalent. 

```
/myObject/getSometing()
/myObject/something()
/myObject/something
```
Method items can have one but [only one parameter provided](faq.md#why-only-one-parameter-for-method-path-items) (for now) : Method items aims to access getter method. one parameter is all we need in that case.  

Following paths are equivalent. 
```
/myObject/getSometing("param")
/myObject/something("param")
```
Path item allowing to access directly to a PHP element is called an accessor. There is some other types of path items (keywords and symbols) we'll see later.
## Query, a quick overview

We talked about path till now ? flub! we would have talk about query !

in fact, the second parameter Grabbag::grab method accepts is not a path but a query.
All will be explained as soon as i tell you the simplest form of a query is a path.

So now the question is : what a query could be if it's non (only) a path !

### path-arrays

path-arrays gathers paths in order to produce structured arrays.

**Example :**
```php
$result = Grabbag::grab($subject, [
    "my/first/path",
    "my/second/path"
]);
```
**Example result  :** 
```php
['my value #1', 'my value #2',]
```
In query, paths are often prefixed with a path id (See Path ids) allowing to produce a keyed value in the result.

## Path ids

### Usage

Path ids are used to identify a path in a query and are located on start of the path and ends with a ':'

It can have 2 usages : 

* Usage 1 : It can be used to specify the value key in the result scope
* Usage 2 : It can be used in a __modifier__ to refer to a path value.

### Explicit or internal id

By default, a path id is explicit. It means id is used in the result scope as a key for the value(s) the path collect.

If you want to avoid the key to be used in result scope you must prefix it with "~". Such ids are called internal ids.

Usage 1 example : 

```php
$result = Grabbag::grab($subject, [
    "lv-1:my/first/path", //this is an explicit id
    "~lv-2:my/second/path", //this is an internal id
    "lv-3:my/first/path"
]);
```
Result example :
```php
[
     "lv-1" => "Result 1",
     "Result 2",
     "lv-3" => "Result 3"
]
```
### Ids and modifiers

Internal or explicit ids can be used with modifiers to alter the path result.

An example using transform modifier :

```php
$result = Grabbag::grab($subject, [
    "lv-1:my/first/path", //this is an explicit id
    "~lv-2:my/second/path", //this is an internal id
    "lv-3:my/third/path/goes/here",
    "?transform" => function($value, $id){
        if($id === "~lv-2"){
            return '>>>' . $value . '<<<';
        }
    }
]);
```
```php
[
     "lv-1" => "Result 1",
     ">>>Result 2<<<",
     "lv-3" => "Result 3"
 ]
```
## Query, let's go further

We've seen that the simplest form of a query is a Path, 
We've seen also a query can be a path-array, using or not path-ids.

Fine. Let's make a step ahead with embedded path-arrays.

### Embedded path-arrays

In a query, path-arrays can be embedded in order to produce leveled structured results. 
Each path-array can contain paths or embedded path-arrays that will be resolved to produce results in the 
related result scope.

Embedded path-arrays example : 

```php
$result = Grabbag::grab($subject, [
    "my-key-A:my/first/path",
    "my-key-B:my/second/path" => [
        "my-key-AA:continue/here",
        "my-key-AB:continue/there"
    ]
    "my-key-C:my/first/path",
]);
```
### Result scope

Result scope is an important Grabbag concepts.

Let consider the following path : 
```php
$result = Grabbag::grab($subject, 'my/object/%any/continues/%any');
```

The result would be something like this : 
```php
[
    "My string 1-1",
    "My string 1-2",
    "My string 2-1",
    "My string 2-2"
]
```
If we now split this path in a query containing 2 embedded path-array like this :
 
```php
$result = Grabbag::grab($subject, [ 
    
    // path-array #1
    "my/object/%any" => [ 
        
        // path-array #2
        "continues/%any"
    ]
]
```

We request same objects pretty the same (my/object/%any/continues/%any), but we plitted it and added an embedded path-array having its own result scope. The whole result will now be something like this :


```php
[ // Result scope #1
    [ // Result scope #2
        "My string 1-1",
        "My string 1-2"
    ],
    [ // Result scope #2
        "My string 2-1",
        "My string 2-2"
    ]
]
```

see the [source code here](../examples/1-my-first-embedded-path-array/example.php);

### Single value path-array.

It's sometimes convenient to have result scope not producing an array but a single value when the expected value is obviously single.

example :

```php
$result = Grabbag::grab($subject, 'this/is/my/path');
```
Same query with a path-array: 
```php
$result = Grabbag::grab($subject, ['this/is/my/path']);
```

Regarding path-array logic, this latter query (having a path-array) would normally produce an array. But is it of use ?

* The path is obviously not returning multiple values (no %any or such in path expression)
* the path does not have multiple path neither path id producing a keyed value in result scope.

In this case default behavior is : the value returned is the value itself an not an array containing the single value.

The principle applies also on embedded path-arrays. The previous example is equivalent to next ones:

```php
$result = Grabbag::grab($subject, [
    "this/is" => [
        "my/path"
    ]
]);
```
```php
$result = Grabbag::grab($subject, [
    "this" => [
        "is/my/path"
    ]
]);
```
```php
$result = Grabbag::grab($subject, [
    "this" => [
        "is" => [
            "my" => [
                "path"
            ]
        ]
    ]
]);
```

All Queries above works and return an identical result, even if there is no point to use last solutions as-is.

* Usage : * 

you can couple this with modifiers, for instance to define a default value.

```php 
$result = Grabbag::grab($subject, [
    "~value:this/is/my/path",
    "?default-value" => "oh sorry nothing there !"
]);
```

More complex example it can be useful used with exception-enabled modifier for example :

```php
$result = Grabbag::grab($subject, [
    "this" => [
        "is/my" => [
            "path",
            exception-enabled:TRUE    
        ],
        exception-enabled:FALSE
    ],
    exception-enabled:TRUE,
    "?default-value" => "oh sorry nothing there !"
]);
```
In this very last example, this/is/my/path will be resolved, 
if path can't be resolved on "this" or "path" parts, an exception will be thrown.
if path can't be resolved on "is" or "my" parts, the default value will be returned.

This last one is a bit tricky an can be useful on very special cases but it illustrate how embedded single-matching-path-arrays can be used.
 
Forcing single-matching path-array to produce array.

If for any reason you want to force an single-matching path-array to produce an array and not a single value you can force it using the modifier ?keep-array :

```php
$result = Grabbag::grab($subject, [
    "~value:this/is/my/path",
    "?keep-array"
]);
```

## Symbols

* . : The element corresponding to the current path item
* .. : The element corresponding to the previout path item

## Keywords

Keywords are path items prefixed by "#" that implements special behaviors.

* [\%any](path-keywords/any-keyword.md)
* [\%key](path-keywords/key-keyword.md)

## Modifiers

In a query, path-arrays can contain modifiers.
Modifiers are prefixed using "?" and allows to alter values or behavior inside the path-array.

### Global or Path modifiers

There is different kind of modifiers : 

* **Gobal modifier** that applies on the global path-array such as ?unique, ?keep-array, ?exception-enabled.
* **Path modifiers** that applies on a given path in the path array such as ?transform, ?consider, ?default-value.

### Un-targeted/targeted Path modifiers

Path modifiers can have an un-targeted and targeted syntax.

Un-targeted syntax : 

```php
$result = Grabbag::grab($subject, [
~myId:this/is/my/path,
"?my-path-modifier" => "whatever my modifier takes as an input"
]);
```

Targeted syntax : 

```php
$result = Grabbag::grab($subject, [
~myId:this/is/my/path,
"?my-path-modifier@~myId" => "whatever my modifier takes as an input"
]);
```

How does that works

a targeted path modifiers is used for path having its id specified after the "@" char in the modifier name. 
If there is no targeted path modifier, the un-targeted path modifier will be used if exists.

for un-targeted modifier triggering a callback function, the path-id is passed as a function argument.


### All Path modifier

* [?unique](query-modifiers/unique-modifier.md)
* [?transform](query-modifiers/transform-modifier.md)
* [?consider](query-modifiers/consider-modifier.md)
* [?keep-array](query-modifiers/keep-array-modifier.md)
* [?default-value](query-modifiers/default-value-modifier.md)
* [?exception-enabled](query-modifiers/exception-enabled-modifier.md)
* [?debug](query-modifiers/debug-modifier.md)




