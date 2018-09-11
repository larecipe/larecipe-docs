# Upgrade

We attempt to document every possible breaking change. Moreover, you can find more details about each release in the [CHANGELOG](https://github.com/saleem-hadad/larecipe/blob/master/CHANGELOG.md).

---

- [Features](#features)
- [Config](#config)
- [UI](#ui)

<a name="features"></a>
## Features

No actual core breaking changes from `v1.0` to `v1.1`. However, there are some cool features added to `v1.1`:

##### <i class="fa fa-bar-chart"></i> Google Analytics Support

Yeah! now you can track your docs easilly by integrating Google Analytics in one step. See [**configuration**](/docs/{{version}}/configurations#settings) page for more details.

##### <i class="fa fa-address-card"></i> Authorization

A simple way to authorize user access against a given documentation. See [**authorization**](/docs/{{version}}/authorization) page for more details.

<a name="config"></a>
## Config

Few enhancements have been added to `v1.1`

1. Google Analitics Id: `ga_id` key has been added to the `settings` array.

```php
return [
    'settings' => [
        'auth'  => false,
        'ga_id' => ''
    ]
];
```

2. Allow to opt out app name in navbar: `show_app_name` key has has been added to the `ui` array.

```php
return [
    'ui'                 => [
        'show_app_name'  => true,
        ...
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

There are some new enhancements and fixes added to `v1.1`. In order to apply them you can run:

```php
php artisan vendor:publish --tag=larecipe_assets --force
```