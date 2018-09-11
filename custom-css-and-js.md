# Custom CSS and JS

Wants extra features and style?. No problem, dude. 

---

- [How To](#how-to)

<a name="how-to"></a>
## How to?

LaRecipe allows you add your own custom CSS and JS in order to change the behaviour or the style of the UI elements to match your needs. Simply pass the path of your CSS and JS files in the corresponding arrays in the `config/larecipe.php`.

> {info} You can pass multiple CSS/JS files at once.

```php
return [
    'ui'                 => [
        ...
        'additional_css' => [
            //'css/custom.css',
        ],
        'additional_js'  => [
            //'js/custom.js',
        ],
    ]
];
```