---
title: Authorization
description: 'In addition to providing authentication feature out of the box, LaRecipe also provides a simple way to authorize user access against a given documentation.'
---

# Authorization

In addition to providing [authentication feature](configurations.md#settings) out of the box, LaRecipe also provides a simple way to authorize user access against a given documentation.

## Why? {#why}

I use LaRecipe sometimes to write documentation for our clients' projects and share the access with them. However, in some situations, we want to prevent a group of users access against specific resources for several reasons.

## How? {#how}

LaRecipe provides an easy way to activate the authorization feature using Laravel Gate. Please have a look at the [official documentation](https://laravel.com/docs/authorization) if you're not familiar with Gates.

Register a new gate permission `viewLarecipe` within the `boot` method in your `AuthServiceProvider`.

```php
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('viewLarecipe', function($user, $documentation) {
            return true;
        });
    }
}
```

## Examples {#examples}

<Badge tone="info">1</Badge>

You might check if the current user has a certain role like `isAdmin()` in order to access all docs.

```php
Gate::define('viewLarecipe', function($user, $documentation) {
    return $user->isAdmin();
});
```

<Badge tone="info">2</Badge>

You may want to limit access for a small group of people listed manually.

```php
Gate::define('viewLarecipe', function($user, $documentation) {
    return in_array($user->email, [
        'saleem@test.com',
        'admin@test.com',
    ]);
});
```

<Badge tone="info">3</Badge>

You may want to give access to all users except one person where his access is limited to a few pages only.

```php
Gate::define('viewLarecipe', function($user, $documentation) {
    if($user->email == 'saleem@test.com')
    {
        if($documentation->title == 'Overview') {
            return true;
        }
        
        return false;
    }
    
    return true;
});
```

<Badge tone="info">4</Badge>

You may want to allow guest users to see some pages, authenticated users to see others, and admins (or any other groups you can think of) to see the rest. Note: you'll need to follow the steps explained in [configuration settings](configurations.md#settings) to accomplish this.

```php
Gate::define('viewLarecipe', function(?User $user, $documentation) {
    if($user && !$user->isAdmin()){
        //do some logic for regular authenticated users
        if($documentation->title == 'Overview'){
            return true;
        }
        return false;
    }else if($user && $user->isAdmin()){
        //do some logic for admin users
        if($documentation->title == 'Admin Dashboard'){
            return true;
        }
        //or maybe just default to true
        return true;
    }else{
        //do some logic for guest users
        if($documentation->title == 'Features'){
            return true;
        }
        return false;
    }
});
```
