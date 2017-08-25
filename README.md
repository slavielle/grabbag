Grabbag is a PHP library that aims provide a simple secure way to request PHP objects chains.

# Features :
* Compact path like syntax using uniform syntax for getter, method or property
* Prevent exception while accessing objects chains and provide a default value when a path cannot be resolved
* multiple value result using #each
* multi-level result using path array

# A first example

Example from Drupal 8 : getting the image URL from a node using entity reference field pointing to a media entity would give someting like this :

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
* Raw PHP method is not secure : Some of the methods/properties can return/be NULL in some case and then cause an exception.
* Grabbag Method is secure. If it's not possible to walk along the object chain, grab method will return NULL or a default value to be specified.

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



 
