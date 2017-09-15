# JSON, friends and fruits !

This example shows **how to use Grabbag to transform JSON** to another JSON structured a different way.

It demonstrates how to use the **?unique** modifier too.

### Example Case : 

We have a [JSON file](data.json) containing a list of friends containing for each one the list of fruits they like.

We would like to generate another JSON containing the full list of those friends, and the full list of all the fruits they like :

```json
{
    "my-friends": [
        "John",
        "Mary"
    ],
    "fruits-they-like": [
        "apple",
        "banana",
        "orange",
        "strawberry",
        "cherry",
        "orange"
    ]
}
```
### Solution 


The data transformation part can be done using a simple query (above) wrapped with PHP Json encode/decode functions.

```php
[
    'my-friends:#any/name',
    'fruits-they-like:#any/food/liked/fruits/#any' => [
        '.',
        '?unique'
    ]
]
```

[See the full example code](index.php)
