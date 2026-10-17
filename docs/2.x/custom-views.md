---
title: 'Custom Views'
description: 'Want to customize the look and feel of your documentation? Easy! LaRecipe gives you an easy place to adjust the blade files.'
---

# Custom Views

---

LaRecipe publishes a set of blade files that you can edit. These files can be found in:

```bash
resources/views/vendor/larecipe/partials
```

- [404](#404)
- [Logo](#logo)
- [Navigation Bar](#nav)
- [Sidebar](#sidebar)

## 404 {#404}

The `404.blade.php` file contains the image and text presented when someone tries to navigate to a page in your documentation that doesn't exist.

## Logo {#logo}

The `logo.blade.php` file contains the image that is displayed in the top left hand corner of your navigation bar. You can change the code in here to insert, for example, your company logo.

## Navigation Bar {#nav}

The `nav.blade.php` file contains the code for the navigation bar. If you want to make a change, for example removing the github button, this is the place to do that.

## Sidebar {#sidebar}

The `sidebar.blade.php` file is the place where you can customize the sidebar menu. Out of the box this file just looks at your `docs/index.md` file, but you are free to customize to your hearts content.
