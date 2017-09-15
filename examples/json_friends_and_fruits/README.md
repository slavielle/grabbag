# JSON, friends and fruits !

This example shows **how to use Grabbag to transform JSON** to another JSON structured a different way.
Is demonstrate how to use the **?unique** modifier too.

### Example Case : 

We have a [JSON file](data.json) containing a list of friends containing for each one the list of fruits they like.

We would like to generate another json containing the full list of those friends, and the full list of all the fruits they like :

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
[See example code](index.php)
