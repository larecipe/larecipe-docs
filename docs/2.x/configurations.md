---
title: 'LaRecipe Configurations And General Setup'
description: 'With the installation of LaRecipe you will find a new configuration file located at config/larecipe.php. In this file, you can find various options to change the configuration of your LaRecipe installation.'
---

# LaRecipe Configurations And General Setup

## Routing {#routing}

LaRecipe by default assumes that you want to use it for creating documentations, for that the default route is set to `/docs`. Moreover, you can set the default path of your markdown docs, by default it assumes `/resources/docs`.

> [!WARNING]
> LaRecipe will try to locate `index.md` in each of your documentations versions in order to generate the sidebar index. You can create the files manually or you can generate them using LaRecipe docs generator.

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

> [!NOTE]
> The landing page is used to redirect your users to it if they visit your docs root route without specifying the page, by default it assumes `overview.md`.

```php
return [
    'docs'      => [
        'route'   => '/docs',
        'path'    => '/resources/docs',
        'landing' => 'overview',
        'middleware' => ['web']
    ]
];
```
> [!IMPORTANT]
> Want to be able to access Laravel's `Auth` class from within any of the published LaRecipe blades? Be sure to set the `larecipe.docs.middleware` array to have `web` in it, as shown above.

## Versions {#versions}

Here you may specify the published versions of your documentation that users can view and the default version to be redirected to if your users visit the root route of your docs without specifying the intended page.

> [!WARNING]
> Make sure to list the default version is in the published versions list.

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

## Settings {#settings}

These options configure the additional behaviors of your documentation where you can limit the access to only authenticated users in your system, or, using middleware, any combination you want. Moreover, you can setup Google Analytics service by adding your `ga_id`.

> [!NOTE]
> When you set the auth to true, LaRecipe will activate Laravel's web auth middleware on the entire docs. But, when you set  it to false, LaRecipe will look at the middleware you have configured here to determine whether or not your docs are shown.

```php
return [
    'settings' => [
        'auth'  => false,
        'ga_id' => '',
        'middleware' => [
            'web',
        ]
    ]
];
```

Digging deeper on setting `auth` to false and and making your own middleware: Let's say you wish to show the same version of documentation to 

- guest users
- authenticated users and
- a subset(s) of authenticated users

 Each group can only view a certain portion or pages of the docs within your published version. To accomplish this, you set `settings.auth` to false and `settings.middleware` to web and your new middleware you made, such as `['web','docs']`. Once this complete, you can use [gates](authorization.md) to specifically decide which user (or guest!) sees which documentation pages.

> [!NOTE]
> Remember, if you set `auth` to true, your `settings.middleware` will be ignored!.

## Cache {#cache}

Obviously rendering markdown at the back-end is costly especially if the rendered files are massive. Thankfully, caching is considered as a good option to speed up your app when having high traffic. However, this option is not enabled by default.

> [!IMPORTANT]
> Caching period unit: `minutes`

```php
return [
    'cache'       => [
        'enabled' => false,
        'period'  => 5
    ]
];
```

## Search {#search}

Here you can add configure the search functionality of your docs. You can choose the default engine of your search from the list. However, you can also enable/disable the search's visibility. Supported Search Engines: `algolia`, `internal`

> [!WARNING]
> [`Algolia DocSearch`](https://community.algolia.com/docsearch/) requires you to submit your site to them for indexing before it starts working.

For more information, refer to Algolia DocSearch's [`documentation`](https://community.algolia.com/docsearch/what-is-docsearch.html)

```php
return [
    'search'            => [
        'enabled'       => false,
        'default'       => 'algolia',
        'engines'       => [
            'internal'  => [
                'index' => ['h2', 'h3']
            ],
            'algolia'   => [
                'key'   => '',
                'index' => ''
            ]
        ]
    ]
];
```

## Appearance {#ui}

Here you can add configure the appearance of your docs. For example, you can set the primary and secondary colors that will give your docs a unique look.

```php
return [
    'ui'                 => [
        'code_theme'     => 'dark', // or: light
        'fav'            => '',     // eg: fav.png
        'colors'         => [
            'primary'    => '#787AF6',
            'secondary'  => '#2b9cf2'
        ]
    ]
];
```

## SEO {#seo}

These options configure the SEO settings of your docs. However, LaRecipe by default sets the canonical link to the viewed page's link automatically.

```php
return [
    'seo'                 => [
        'author'          => '',
        'description'     => '',
        'keywords'        => '',
        'og'              => [
            'title'       => '',
            'type'        => 'article',
            'url'         => '',
            'image'       => '',
            'description' => '',
        ]
    ]
];
```

## Forum {#forum}

Giving a chance to your users to post their questions or feedback directly on your docs, is pretty nice way to engage them more. However, you can also disable the forum's visibility if you don't like the idea. Supported Services: `disqus`

> [!NOTE]
> [`Disqus`](https://disqus.com/) is a great way to setup forum inside your website/docs in few steps. Create a site and paste its name in the `site_name` to activate the disqus forum.

```php
return [
    'forum'                 => [
        'enabled'           => false,
        'default'           => 'disqus',
        'services'          => [
            'disqus'        => [
                'site_name' => '', // yoursite.disqus.com
            ]
        ]
    ]
];
```
