# Eloquent Temp Tag



Temporarily and Transparently, tag your eloquent models



```

composer require imanghafoori/laravel-temp-tag

```



### Use cases:

- You wanna ban a user but only for a week as punishment.

- You wanna give someone VIP access but for only a month.

- You wanna activate a Coupon code to be usable for a day.



Then you put a temporary tag on them and check to see if the tag is 



and many other use cases.



### Keynotes:

- Tags can also be permanent.

- We do not touch your existing tables in migrations.

- There is no need to put traits on your models.

- You can put tag on any eloquent model or any other object with `id` property and unique returning `getTable` method.



### Usage:



1- Tag it until tomorrow

```php

  $user = User::find(1);

  $tomorrow = Carbon::now()->addDay();



  tempTags($user)->tagIt('banned', $tomorrow); 

  tempTags($user)->tagIt('banned', null);   // Converts to Permanent ban !

```



2- After an hour the tag is still active, so:

```php

    $tagObj = tempTags($user)->getTag('banned');

    

    $tagObj->isActive();        // true

    $tagObj->isPermanent();     // false

    $tagObj->title === 'banned' // true

    $tagObj->expiresAt();       // Carbon instance

```



3- After a week the tag is died out, so:

```php

    $tagObj = tempTags($user)->getTag('banned');



    $tagObj->isActive();        // false

    $tagObj->isPermanent();     // false

    $tagObj->title === 'banned' // true

    $tagObj->expiresAt();       // Carbon instance

```



Deleting tags:

```php

    tempTags($user)->unTag('banned');          // single string

    tempTags($user)->unTag(['banned', 'man']); // an array of tags to delete

    

    tempTags($user)->deleteExpiredTags();     // all the expited tags, bye bye.

```

These fire "deleting" and "deleted" eloquent events for each and every one of them.


Manually expire the tag with title of "banned":

```php

   tempTags($user)->expireNow('banned');      // updates the value of "expire_at" to now()

```


These methods just do what they say:

```php

    $actives = tempTags($user)->getAllActiveTags();

    $expired = tempTags($user)->getAllExpiredTags();

    $expired = tempTags($user)->getAllTags();

```


--------------------


### :raising_hand: Contributing:

If you find an issue or have a better way to do something, feel free to open an issue or a pull request.

If you use laravel-widgetize in your open source project, create a pull request to provide its URL as a sample application in the README.md file. 



### :exclamation: Security:

If you discover any security-related issues, please use the `security tab` instead of using the issue tracker.



### :star: Your Stars Make Us Do More :star:

As always if you found this package useful and you want to encourage us to maintain and work on it. Just press the star button to declare your willingness.


## More from the author:


### Laravel Microscope


:gem: It automatically find bugs in your laravel app



- https://github.com/imanghafoori1/laravel-microscope



-------------

### Laravel HeyMan

:gem: It allows you to write expressive code to authorize, validate, and authenticate.


- https://github.com/imanghafoori1/laravel-heyman


--------------


<p align="center">

 
    It's not that I'm so smart, it's just that I stay with problems longer.


    "Albert Einstein"
    

</p>

