Grabbag is a PHP library that aims provide a simple secure way to request PHP objects chains using path like expressions.

# Features :
* Compact path like syntax using uniform syntax for getter, method or property
* Prevent exception while accessing objects chains and provide a default value when a path cannot be resolved
* Multiple value result using #each
* Structured result using path array

# A first example

Lets take an example from Drupal 8 : Getting the image URL from a node using entity reference field pointing to a media entity would give someting like this :

## Raw PHP
```php
$node->get('field_media_image')->first()->get('entity')->getTarget()->getValue()->get('field_image')->entity->getFileUri()
```

## Using Grabbag
```php
$grabber = new Grabber($node);
$result = $grabber->grab('get("field_media_image")/first/get("entity")/target/value/get("field_image")/entity/fileUri');
echo $result->getValue();
```

## Comparition : 
* Raw PHP expression is not implicitly secure : Some of the methods/properties along the expression can return or be NULL in some case and then cause an exception. If you really want to secure expression, you would test some of the values before accessing them.
* Grabbag expression is implicitly secure. If it's not possible to walk along the object chain, grab method will return NULL or a default value to be specified.

# Multiple value result using #each

Path can collect more than one simple value using #each keyword.
Let's consider the following example that looks like the previous one except for the #each
```php
$grabber = new Grabber($node);
$result = $grabber->grab('get("field_media_image")/#each/get("entity")/target/value/get("field_image")/entity/fileUri');
echo $result->getValue();
```
if the value corresponding to #each in the path can be iterated (if it's an array or an object implementing [Iterator interface](http://php.net/manual/en/class.iterator.php) for instance), #each will resolve the path considering each one of these values.

For instance if 
```php
$node->get('field_media_image') 
```
contains 4 items, then the result value will be an array looking like this : 
```php
["my/image/1.jpg", "my/image/2.jpg", "my/image/3.jpg", "my/image/4.jpg"]
```
# Structured result using path array
Path arrays allow to go a bit further.
A path array is an array gathering paths in order to produce structured arrays.
Lets take an example : 

```php
$grabber = new Grabber($node);
$result = $grabber->grab([
    'contentTitle:get("title").value',
    'images:get("field_media_image")/#each/get("entity")/target/value/get("field_image")' => [
        'uri:entity/fileUri',
        'alt:alt'
    ]
 ]);
echo $result->getValue();
```
will produce a structured array such as : 
```php
[
    'contentTitle' => 'My node title', 
    'images' => [
        [
            'uri' => "my/image/1.jpg"
            'alt' => "My image 1 alt"
        ],
        [
            'uri' => "my/image/2.jpg"
            'alt' => "My image 2 alt"
        ],
                [
            'uri' => "my/image/3.jpg"
            'alt' => "My image 3 alt"
        ],
    ]
]
```
# Field of use

Grabbag can be used in all PHP project using object intensively. This inclure project build on top of some of the most popular PHP framework or CMS such as Drupal 8, Symfony, Laravel and so on ...

It can be used everywhere you would have to extract values from objects in order to extract structured array. for example: 
* Producing array for json Rest web-services.  
* Producing array for twig templates.
