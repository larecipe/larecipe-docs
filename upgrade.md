# Upgrade

We attempt to document every possible breaking change. Moreover, you can find more details about each release in the [CHANGELOG](https://github.com/saleem-hadad/larecipe/blob/master/CHANGELOG.md).

---

- [Features](#features)
- [Config](#config)
- [UI](#ui)

<a name="features"></a>
## Features

No actual core breaking changes from `v1.1` to `v1.2`. However, there are some cool features and enhancements added to `v1.2`:

##### <i class="fa fa-search"></i> Algolia Search Support

You can now use Algolia DocSearch directly in your docs simply by passing your key and index. See [**configuration**](/docs/{{version}}/configurations#search) page for more details.

<a name="config"></a>
## Config

Few enhancements have been added to `v1.2`


1. Search Support : `search` array section has been added to the config.

```php
return [
    'search'            => [
        'enabled'       => false,
        'default'       => 'algolia',
        'engines'       => [
            'algolia'   => [
                'key'   => '',
                'index' => ''
            ]
        ]
    ]
];
```

2. Dynamic color palette : `colors` array has been added to the `ui` array.

```php
return [
    'ui'                 => [
        ...
        'colors'         => [
            'primary'    => '#787AF6',
            'secondary'  => '#2b9cf2',
            'selection'  => '#f4f5f7'
        ],
        ...
    ]
];
```

3. Sidebar visibility can be configured: `show_side_bar` has been added to the `ui` array.

```php
return [
    'ui'                 => [
        ...
        'show_side_bar'  => true,
    ]
];
```

4. Dark/Light code theme: `code` has been added to the `ui` array.

```php
return [
    'ui'                 => [
        ...
        'code'           => 'dark',
    ]
];
```

5. Better SEO support: `og` array has been added to the `seo` array.

```php
return [
    'seo'                 => [
        ...
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


6. Add disqus fourm support: `fourm` array section has been added to the config.

```php
return [
    'fourm'                 => [
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

You can add them manually or you can run this command:

> {warning} Please note running the command with `--force` will override your config settings.

```php
php artisan vendor:publish --tag=larecipe_config --force
```

<a name="ui"></a>
## UI

There are some new enhancements and fixes added to `v1.2`. In order to apply them you can run:

> {info} You might notice when run this command the `fonts` folder is added again to the `public/vendor/binarytorch/assets`, see [`#23`](https://github.com/saleem-hadad/larecipe/pull/23). Therefor, you can now delete the old `public/fonts` manually if you want.

```php
php artisan vendor:publish --tag=larecipe_assets --force
```
