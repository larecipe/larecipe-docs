# Upgrade

We attempt to document every possible breaking change. Moreover, you can find more details about each release in the [CHANGELOG](https://github.com/saleem-hadad/larecipe/blob/master/CHANGELOG.md).

---

- [Features](#features)
- [Config](#config)
- [UI](#ui)

<a name="features"></a>
## Features

No actual core breaking changes from `v1.2` to `v1.3`. However, there are some cool features and enhancements added:

##### <i class="fa fa-search"></i> Internal Search Support

You can now use internal search engin simply by activating it and choosing it as default engin. See [**configuration**](/docs/{{version}}/configurations#search) page for more details.


<a name="config"></a>
## Config

Few enhancements have been added to `v1.3`


1. Search Support : `search` array section has been updated to:

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
    ],
];
```

2. Settings section updated to include `auth_links`:

```php
return [
    'settings' => [
        'auth'  => false,
        'auth_links' => [
            [
                'name' => '',
                'url' => '',
            ],
        ],
        'ga_id' => ''
    ],
];
```

You can add them manually or you can run this command:

> {warning} Please note running the command with `--force` will override your config settings.

```php
php artisan vendor:publish --tag=larecipe_config --force
```

<a name="ui"></a>
## UI

There are some new enhancements and fixes added to `v1.3`. In order to apply them you can run:

```php
php artisan vendor:publish --tag=larecipe_assets --force
```
