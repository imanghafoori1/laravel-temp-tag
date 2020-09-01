# Eloquent Temp Tag

Temporarily and Transparently, tag your eloquent models

### Use cases:
- You wanna ban a user but only for a week as punishment.
- You wanna give someone VIP access but for only a month.
- You wanna activate a Coupon code to be usable for a day.

Then you put a temporary tag on them and check to see if the tag is 

and many other use cases.

### Key notes:
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

2- After an hour
```php
    $tagObj = tempTags($user)->getTag('banned');
    
    $tagObj->isActive();        // true
    $tagObj->isPermanent();     // false
    $tagObj->title === 'banned' // true
    $tagObj->expiresAt();       // Carbon instance
```

3- After a week
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
```

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
