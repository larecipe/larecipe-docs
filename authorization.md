# Authorization

In addition to providing [authentication feature](/docs/{{version}}/configurations#settings) out of the box, LaRecipe also provides a simple way to authorize user access against a given documentation.

---

- [Why?](#why)
- [How?](#how)
- [Examples](#examples)

<a name="why"></a>
## Why?

We at [BinaryTorch](https://binarytorch.com.my/) we use LaRecipe internally to write documentation for our clients' projects and share the access with them. However, in some situation, we want to prevent a group of users access against specific resources for several reasons.

<a name="how"></a>
## How?

LaRecipe provides an easy way to activate the authorization feature using Laravel Gate. Please have a look at the [official documnetation](https://laravel.com/docs/5.7/authorization) if you're not familiar with Gates.

Register a new gate within `boot` method in your `AuthServiceProvider`.

> {warning} Please note that once you define `viewLarecipe` gate, `Laravel` automatically will activate `auth` middleware on the entire docs which means your docs will be accessible only by authenticated users and then the authorization gate will be applied to the users.

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

<a name="examples"></a>
## Examples

<larecipe-badge type="primary" circle class="mr-3 mb-2">1</larecipe-badge>

You might check if the current user has admin role in order to access all docs.

```php
Gate::define('viewLarecipe', function($user, $documentation) {
    return $user->isAdmin();
});
```

<larecipe-badge type="primary" circle class="mr-3 mb-2 mt-6">2</larecipe-badge>

You may want to limit the access for small group of people listed manually.

```php
Gate::define('viewLarecipe', function($user, $documentation) {
    return in_array($user->email, [
        'saleem@binary-torch.com',
        'admin@binary-torch.com',
    ]);
});
```

<larecipe-badge type="primary" circle class="mr-3 mb-2 mt-6">3</larecipe-badge> 

You may want to give access to all users except one person where his access limited to few pages only.

```php
Gate::define('viewLarecipe', function($user, $documentation) {
    if($user->email == 'saleem@binary-torch.com')
    {
        if($documentation->title == 'Overview') {
            return true;
        }
        
        return false;
    }
    
    return true;
});
```
