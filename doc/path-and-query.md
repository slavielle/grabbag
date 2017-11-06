# Path & query principles

* [Path principle](#path-principle)
    * [Path items](#path-items)
* [Query, a first overview](#query-a-first-overview)
    * [path-arrays](#path-arrays)
    * [Path ids](#path-ids)
        * [Usage](#usage)
        * [Explicit or internal id](#explicit-or-internal-id)
        * [Ids and modifiers](#ids-and-modifiers)     
* [Query, let's go further](#query-lets-go-further)
    * [Embedded path-arrays](#embedded-path-arrays)
    * [Single value path-array](#single-value-path-array)
* [Symbols](#symbols)
* [Keywords](#keywords)
* [Modifiers](#modifiers)


## Path principle

Grabbag offers an alternative to raw PHP object/array chain expressions by using path that acts similarly but with some benefits.

for example, a PHP chain such as : 
```php
$result = $element->getThat()->is['a']['path'];
```
can be written this way using Grabbag : 
```php
$result = Grabbag::grab($element, 'that/is/a/path');
```

Grabbag path syntax is directly [inspired from Linux path syntax](faq.md#why-choosing-linux-path-like-syntax). That was made on purpose to look familiar for developers and make Grabbag basic usage as easiest as possible.


### Path items

Path items are separated by slashes an refer to PHP elements (array, array key, object, object property, method).

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
When a *path item* refers to an object getter method, the leading 'get' can be omitted so can be the parenthesis if no parameter is required.

Following paths are equivalent. 

```
/myObject/getSometing()
/myObject/something()
/myObject/something
```
Method items can have one but [only one parameter provided](faq.md#why-only-one-parameter-for-method-path-items) (for now) : Method items aims to access getter method : one parameter is all we need in that case.  

Following paths are equivalent. 
```
/myObject/getSometing("param")
/myObject/something("param")
```
*Path items* refering directly to a PHP element are called *accessors*. There is some other types of *path item* (*keywords* and *symbols*) that will be detailed later.

## Query, a first overview

We talked about *path* till now, but we should have talked about *query* : The second parameter ```Grabbag::grab``` method accepts is in fact a *query*:
A path is just the simpler form query can take - to keep things clear we talked about path.

So now let's see what query could be if it's not only a path !

### path-arrays

A *path-array* is meant to gather *paths* in order to produce an array of result values.

Example :
```php
$result = Grabbag::grab($subject, [
    "my/first/path",
    "my/second/path"
]);
```
Example result  :
```php
['my value #1', 'my value #2'];
```
In *query*, paths are often prefixed with *path ids* allowing to produce a keyed value in the result.

### Path ids

#### Usage

*Path ids* are used to identify a *path* in a *path array*. They are located before the *path* itself, end with a ':' and are optional.

They main usage is to specify a keyed value in the result array.

Example :
```php
$result = Grabbag::grab($subject, [
    'id-1:my/first/path',
    'id-2:my/second/path'
]);
```
Example result  :
```php
[
    'id-1' => 'my value #1',
    'id-2' => 'my value #2'
];
```

*Path id*s can also be used with *modifiers* to refer a path value, but let's keep this for a bit later.

#### Explicit or internal id

By default, a *path id* is explicit. It means id is used in the result array as a key for the path result value.

If you want to avoid the *path id* to be used as a key in the result array, you must prefix it with "~". Such ids are called internal ids.

Example : 

```php
$result = Grabbag::grab($subject, [
    "id-1:my/first/path", //this is an explicit id
    "~id-2:my/second/path", //this is an internal id
    "id-3:my/first/path"
]);
```
Result example :
```php
[
     "id-1" => "Result 1",
     "Result 2",
     "id-3" => "Result 3"
]
```
#### Ids and modifiers

Internal or explicit *path ids* can be used with *modifiers* to alter the path result.

Example using transform modifier :

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

We've seen that the simplest form of a *query* is a *path*, 
We've seen also a *query* can be a *path array*, using or not *path ids*.

Fine. Let's make a step ahead with *embedded path arrays*.

### Embedded path arrays

In a *query*, *path arrays* can be embedded in order to produce structured results. 

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
If we now split this path in a query containing 2 *path arrays* (one embedded in the other) this way :
 
```php
$result = Grabbag::grab($subject, [ 
    
    // path-array #1
    "my/object/%any" => [ 
        
        // path-array #2
        "continues/%any"
    ]
]
```

we request same objects using the same path (my/object/%any/continues/%any), but each path-array produce its own result array
and the result looks now like this : 

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

It's sometimes convenient to have path not producing an array but a single value when the expected value is obviously single.

Let's consider the following query:

```php
$result = Grabbag::grab($subject, 'this/is/my/path');
```
which is equivalent to: 
```php
$result = Grabbag::grab($subject, ['this/is/my/path']);
```

Regarding path-array logic, this latter query (having a path-array) would normally produce an array. But is it of use ?

* the path is obviously not returning multiple values (no %any or such in path expression),
* the path is not prefixed with an explicit *path id* producing a keyed value in the result array.

In this case the value returned is the value itself an not an array containing the single value.

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

### modifier types

There is different type of modifiers : 

* **Path modifier** that applies on a given path in the path array such as ?transform, ?consider ?call and ?default-value.
* **Path-array modifier** that applies on the all path-array such as ?unique, ?keep-array.
* **Up-propagating modifier** that applies on the current path-array and all of its nested path-array such as ?exception-enabled.

### Un-targeted/targeted Path modifiers

Path modifiers can have an un-targeted and targeted syntax.

#### Un-targeted syntax : 

```php
$result = Grabbag::grab($subject, [
~myId:this/is/my/path,
"?my-path-modifier" => "whatever my modifier takes as an input"
]);
```

#### Targeted syntax : 

```php
$result = Grabbag::grab($subject, [
~myId:this/is/my/path,
"?my-path-modifier@~myId" => "whatever my modifier takes as an input"
]);
```

How does that works

A targeted path modifiers is used for path having its id specified after the "@" char in the modifier name. 
If there is no targeted path modifier, the un-targeted path modifier will be used if exists.

for un-targeted modifier triggering a callback function, the path-id is passed as a function argument.


### All Path modifier

* [?unique](query-modifiers/unique-modifier.md)
* [?transform](query-modifiers/transform-modifier.md)
* [?consider](query-modifiers/consider-modifier.md)
* [?call](query-modifiers/call-modifier.md)
* [?keep-array](query-modifiers/keep-array-modifier.md)
* [?default-value](query-modifiers/default-value-modifier.md)
* [?exception-enabled](query-modifiers/exception-enabled-modifier.md)
* [?debug](query-modifiers/debug-modifier.md)




