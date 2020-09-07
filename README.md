# Laravel Temp Tag

You want to ban a user for a week? Tag it as 'banned' for a week and check the tag in a middleware.


### Installation:

```

composer require imanghafoori/laravel-temp-tag

```

### Use cases:

- You wanna ban a user but only for a week as punishment.

- You wanna give someone VIP access but for only a month.

- You wanna activate a Coupon code to be usable for a day.

- Promote a product in an slider for week.


Then you put a temporary tag on them and check to see if the tag is there.


### Keynotes:

- Tags can also be permanent.

- We do not touch your existing tables in migrations.

- You can put tag on any eloquent model or any other object with `getKey` and `getTable` methods.


### Example Usage:

1- Tag the user until tomorrow

```php

  $user = User::find(1);

  $tomorrow = Carbon::now()->addDay();

  tempTags($user)->tagIt('banned', $tomorrow); 

  tempTags($user)->tagIt('banned');   // Overrides it to Permanent ban!

```

2- After an hour the tag is still active, so:

```php
  $user = User::find(1);
  $tagObj = tempTags($user)->getTag('banned');

  $tagObj->isActive();        // true
  $tagObj->isPermanent();     // false
  $tagObj->title === 'banned' // true
  $tagObj->expiresAt();       // Carbon instance

```

3- After a week the tag is expired out, so:

```php
  $user = User::find(1);
  $tagObj = tempTags($user)->getTag('banned');

  $tagObj->isActive();        // false
  $tagObj->isPermanent();     // false
  $tagObj->title === 'banned' // true
  $tagObj->expiresAt();       // Carbon instance

```

#### Deleting tags:

```php
  $user = User::find(1);
  
  tempTags($user)->unTag('banned');          // single string

  tempTags($user)->unTag(['banned', 'man']); // an array of tags to delete

  tempTags($user)->deleteExpiredTags();     // all the expited tags, bye bye.

```

**Note:** These fire "deleting" and "deleted" eloquent events for each and every one of them.


Expire the tag with title of "banned" right now:

```php

 tempTags($user)->expireNow('banned');      // updates the value of "expire_at" to now()

```


These methods just do what they say:

```php

  $actives = tempTags($user)->getAllActiveTags();  // A collect of "TempTag" model objects.

  $expired = tempTags($user)->getAllExpiredTags();

  $all = tempTags($user)->getAllTags();

```

### Fetch only tagged models:

Lets say you have a slider for your `Product` model and you want to show only those records which are tagged with 'slider'.

First you have to put `Imanghafoori\Tags\Traits\hasTempTags` trait on the `Product` model

```php

class Product extends Model 
{
  use hasTempTags;
  
  ...
}
```

Now you can perform these queries:

```php
Product::hasActiveTempTags('slider')->where(...)->get();

// Only if the tag of model is expired and it has the specified titie.
Product::hasExpiredTempTags('slider')->where(...)->get();

// To fetch regardless of expiration date of tags, only the title matters.
Product::hasTempTags('slider')->where(...)->get();
```

**Note:** If you pass an array of tags it acts like a `whereIn()`, so if the row has one of tags if will be selected.

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

