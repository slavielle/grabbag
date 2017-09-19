# ?transform modifier

Allows to transform a result value using a callback function that shall return the callback value.

[See example](../../examples/2-use-key-keyword/example.php)

Callback function prototype

function($value, $pathId){}

* $value: Value to transform.
* $pathId: Id of the path the value had been matched with.

This modifier is invoked only on path having a path id.