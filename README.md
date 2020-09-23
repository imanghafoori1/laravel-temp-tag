# Laravel Temp Tag

**You want to ban a user for a week? Tag it as 'banned' for a week and check the tag in a middleware.**

![image](https://user-images.githubusercontent.com/6961695/93660285-6a935180-fa62-11ea-98ca-5a7675c6bd6a.png)


[![Latest Stable Version](https://poser.pugx.org/imanghafoori/laravel-temp-tag/v/stable)](https://packagist.org/packages/imanghafoori/laravel-temp-tag)
[![Build Status](https://scrutinizer-ci.com/g/imanghafoori1/laravel-temp-tag/badges/build.png?b=master)](https://scrutinizer-ci.com/g/imanghafoori1/laravel-temp-tag/build-status/master)
[![Total Downloads](https://poser.pugx.org/imanghafoori/laravel-temp-tag/downloads)](https://packagist.org/packages/imanghafoori/laravel-temp-tag/stats)
[![StyleCI](https://github.styleci.io/repos/291741669/shield?branch=master&style=round-square)](https://github.styleci.io/repos/291741669?branch=master&style=round-square)
<a href="https://scrutinizer-ci.com/g/imanghafoori1/laravel-temp-tag"><img src="https://img.shields.io/scrutinizer/g/imanghafoori1/laravel-temp-tag.svg?style=round-square" alt="Quality Score"></img></a>
[![Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=round-square)](LICENSE.md)

<br>
<br>

### Installation:

```

composer require imanghafoori/laravel-temp-tag

```

### Sample Application in laravel 8:

https://github.com/imanghafoori1/laravel-tasks

In this Daily Task app, you can mark your tasks as complete, but they return back to incomplete state at the end of the day, so you can re-do them tomorrow.



### Use cases:

- You wanna ban a user, only for a week.

- You wanna give someone VIP access only for a month.

- You wanna a Coupon code to be usable for until tomorrow.

- You wanna put a product in an slider for week.


Then you put a temporary tag on them and check to see if the model has the tag.

### Tag Payload:

You can also store some additional json data, for example why the user was banned, or who banned the user, or an slug or a translation of the title.

This is done by passing the third argument as an array to the ```->tagIt(...)``` method

### Keynotes:

- Tags can also be permanent.

- We do not touch your existing tables in migrations.

- You can put tag on any eloquent model or any other object with `getKey` and `getTable` methods on it.

--------------

### Example Usage:

1- Tag a user until tomorrow

```php

  $user = User::find(1);
  $tomorrow = Carbon::now()->addDay();
  $note = ['reason' => 'You were nasty!']; // You can optionally store additional data in a json column.

  // Tagging the User:
  tempTags($user)->tagIt('banned', $tomorrow, $note);

  // Or the minimal way:
  tempTags($user)->tagIt('banned');  // will never expire
```

2- After an hour the tag is still active, so:

```php
  $user = User::find(1);
 
  $tagObj = tempTags($user)->getActiveTag('banned');  <--- Uses cache behind the scenes

  $tagObj->isActive();        // true
  $tagObj->isPermanent();     // false
  $tagObj->title === 'banned' // true
  $tagObj->payload            // ['reason' => 'You were nasty!']
  $tagObj->expiresAt();       // Carbon instance
```


3- After a week the tag is expired out, so:

```php
  $user = User::find(1);
  $tagObj = tempTags($user)->getTag('banned');  <--- fetches the tag regardless of its expiration date.

  $tagObj->isActive();        // false
  $tagObj->isPermanent();     // false
  $tagObj->title === 'banned' // true
  $tagObj->expiresAt();       // Carbon instance

```
--------------

Getting payload data:

```php
  $tagObj->getPayload('reason');    //  'You were nasty!'      
  $tagObj->getPayload();            //  ['reason' => 'You were nasty!'] 
  $tagObj->getPayload('missing_key');  //  null
```

--------------

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

 tempTags($user)->expireNow('banned');  // updates the value of expire_at to now() and deletes from cache

```

-------------

These methods just do what they say:

```php

  $actives = tempTags($user)->getAllActiveTags();  // A collect of "TempTag" model objects.

  $expired = tempTags($user)->getAllExpiredTags();

  $all = tempTags($user)->getAllTags();

```

-------------

### Fetch only tagged models:

Lets say you have a slider for your `Product` model and you want to show only those records which are tagged with 'slider'.

First you have to put `Imanghafoori\Tags\Traits\hasTempTags` trait on the `Product` model.

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

// Only if the tag of model is expired and it has the specified title.
Product::hasExpiredTempTags('slider')->where(...)->get();

// To fetch regardless of expiration date of tags, only the title matters.
Product::hasTempTags('slider')->where(...)->get();
```

**Note:** If you pass an array of tags it acts like a `whereIn()`, so if the row has one of tags if will be selected.

-------------

### Absence of tags:

```php
Product::hasNotActiveTempTags('slider')->where(...)->get();

Product::hasNotExpiredTempTags('slider')->where(...)->get();

Product::hasNotTempTags('slider')->where(...)->get();
```

-------------


### Auto-delete Expired tags:

In order to save disk space and have faster db queries you may want to delete the expired tags.

After you have performed the basic installation you can start using the tag:delete-expired command. In most cases you'll want to schedule this command so you don't have to manually run it everytime you need to delete expired tags.

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Will run:  php artisan  tag:delete-expired
    $schedule->command( 'tag:delete-expired' )->everyDay();
}
```

--------------------


### :raising_hand: Contributing:

If you find an issue or have a better way to do something, feel free to open an issue or a pull request.

If you use laravel-widgetize in your open source project, create a pull request to provide its URL as a sample application in the README.md file. 

### :star: Your Stars Make Us Do More :star:

As always if you found this package useful and you want to encourage us to maintain and work on it, just press the star button to declare your willingness.

-------------

## More from the author:


### Laravel Microscope

:gem: It automatically find bugs in your laravel app

- https://github.com/imanghafoori1/laravel-microscope



### Laravel HeyMan

:gem: It allows you to write expressive code to authorize, validate, and authenticate.

- https://github.com/imanghafoori1/laravel-heyman


<br><br>

<p align="center">

 
    It's not that I'm so smart, it's just that I stay with problems longer.


    "Albert Einstein"
    

</p>

