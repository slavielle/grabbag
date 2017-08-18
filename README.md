Grabbag is a library that aims provide a simple secure way to request PHP objects chains

# Features :
* Compact and dotted syntax
* Getter, method, property uniform syntax
* Prevent NULL or exception while accessing object chains and provide a default value
* Multiple objects requesting
* Structured result

# Example

Example from Drupal 8 : getting the image URL from a node using entity reference field pointing to a media entity would give someting like this :

## Raw PHP
```php
$node->get('field_media_image')->first()->get('entity')->getTarget()->getValue()->get('field_image')->entity->getFileUri()
```

## Using Grabbag
```php
$grabber = new Grabber($node);
$result = $grabber->grab('get("field_media_image").first.get("entity").target.value.get("field_image").entity.fileUri');
```

## Comparition : 
* Raw PHP method is not secure : Some of the properties can return NULL in some case causing an exception.
* Grabbag Method is secure. If it's not possible to walk along the object chain, grab method will return NULL or a default value to be specified.
 
