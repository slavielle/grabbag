# ?transform modifier

Allows to transform a result value using a callback function that shall return the callback value.

[See example](../../examples/2-use-key-keyword/example.php)

## Callback function prototype

function($value, $pathId){}

* $value: Value to transform.
* $pathId: Id of the path the value had been matched with.

## Behaviors

This modifier is invoked only on path having a path id.

Callback function always have to return a value event for untouched values! If it don't for some path-id, the value for this pass ID will be replaced by NULL.