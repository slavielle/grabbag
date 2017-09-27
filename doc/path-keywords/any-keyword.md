# %any keyword

Get any values form an array or an object usable with foreach (such as array or instance of class implemented from Iterator Interface)

## Example
```php
$result = Grabbag::grab($subject, 'my/path/with/%any');
```
Result example : 
```php
["value 1", "value 2", "value 3"]
```