---
title: 'Custom CSS and JS'
description: 'Wants extra features and style?. Create a new theme or tool in less than one minute!'
---

# Custom CSS and JS

## New Theme {#new-theme}

LaRecipe allows you to add your own custom assets through custom packages in order to match your needs. Simply run the following command:

> [!NOTE]
> The name of your theme should be in the following format: `vendor-name/project-name`

```bash
php artisan larecipe:theme your-vendor/theme-name
```

After running this command and answering a few questions, a new directory will be added to your Laravel project called `larecipe-components` which will contain any theme/asset you'll create.

## New Tool or Asset {#new-theme}

Creating a new tool using VueJs cannot be easier than running one command:

```bash
php artisan larecipe:asset your-vendor/asset-name
```

Once your package is ready to deploy, you can share it with other developers! [submit your package here](/packages/submit).
