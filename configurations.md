# Configurations

With the installation of LaRecipe you will find a new configuration file located at `config/larecipe.php`.
In this file, you can find various options to change the configuration of your LaRecipe installation.

---

- [Routing](#routing)
- [Versions](#versions)
- [Settings](#settings)
- [Cache](#cache)
- [Repository](#repository)
- [Appearance](#ui)
- [SEO](#seo)

<a name="routing"></a>
## Routing

LaRecipe by default assumes that you want to use it for creating documentations, for that the default route is set to `/docs`. Moreover, you can set the default path of your markdown docs, by default it assumes `/resources/docs`.

> {warning} LaRecipe will try to locate `index.md` in each of your documentations versions in order to generate the sidebar index. You can create the files manually or you can generate them using LaRecipe docs generator.

```php
.
├─ config
│  └─ larecipe.php
└─ resources
   └─ docs
      │─ 1.0
      │  │─ index.md
      │  └─ overview.md
      └─ 2.0
         │─ index.md
         └─ overview.md
```

> {info} The landing page is used to redirect your users to it if they visit your docs root route without specifying the page, by default it assumes `overview.md`.

```php
return [
    'docs'      => [
        'route'   => '/docs',
        'path'    => '/resources/docs',
        'landing' => 'overview'
    ]
];
```

<a name="versions"></a>
## Versions

Here you may specify the published versions of your documentation that users can view and the default version to be redirected to if your users visit the root route of your docs without specifying the intended page.

> {warning} Make sure to list the default version in the published versions list.

```php
return [
    'versions'      => [
        'default'   => '1.0',
        'published' => [
            '1.0'
        ]
    ]
];
```

<a name="settings"></a>
## Settings

These options configure the additional behaviors of your documentation where you can limit the access to only authenticated users in your system. Moreover, you can setup Google Analytics service by adding your `ga_id`.

> {info} When you set the auth to true, LaRecipe will activate Laravel's web auth middleware on the entire docs.

```php
return [
    'settings' => [
        'auth'  => false,
        'ga_id' => ''
    ]
];
```

<a name="cache"></a>
## Cache

Obviously rendering markdown at the back-end is costly especially if the rendered files are massive. Thankfully, caching is considered as a good option to speed up your app when having high traffic. However, this option is not enabled by default.

> {primary} Caching period unit: `minutes`

```php
return [
    'cache'       => [
        'enabled' => false,
        'period'  => 5
    ]
];
```

<a name="repository"></a>
## Repository

This is an optional configuration you can set in order to show the external link to your documentation's repository if you have one. Once you set the value of the `url`, LaRecipe automatically will show the nav button.

```php
return [
    'repository'   => [
        'provider' => 'github',
        'url'      => 'https://github.com/saleem-hadad/larecipe'
    ]
];
```

<a name="ui"></a>
## Appearance

Here you can add configure the appearance of your docs. For example, you can change the default logo, nav and control the visibility of the back-to-top button. Moreover, you can add your own CSS and JS files if you like.

> {primary} Currently Supported Themes: `light`, `dark`

```php
return [
    'ui'                 => [
        'show_app_name'  => true,
        'logo'           => '', // e.g.: /images/logo.svg
        'fav'            => '', // e.g.: /fav.png
        'theme'          => 'light',
        'back_to_top'    => true,
        'additional_css' => [
            //'css/custom.css',
        ],
        'additional_js'  => [
            //'js/custom.js',
        ],
    ]
];
```

<a name="seo"></a>
## SEO

These options configure the SEO settings of your docs. You can set the author, the description, and the keywords. Also, LaRecipe by default sets the canonical link to the viewed page's link automatically.

```php
return [
    'seo'             => [
        'author'      => 'Binary Torch Sdn. Bhd.',
        'description' => 'Write gorgeous docs for your products using Markdown inside your Laravel app.',
        'keywords'    => 'Laravel, docs, api-docs, vue docs'
    ]
];
```