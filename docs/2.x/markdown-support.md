---
title: 'Markdown Support'
description: 'LaRecipe automatically leverages Markdown to HTML parser out of the box.'
---

# Markdown Support

## Basics {#basics}

Markdown elements automatically parsed into normal HTML tags.

<Badge tone="success">Typography</Badge>

Input:

```text
### H3
```

Output:

### H3

<Badge tone="success">Images</Badge>

Input:

```text
![image](url)
```

Output:

![cute image](https://media.tenor.com/images/f45c43d124468dc602a95baabadab70d/tenor.gif)

<Badge tone="success">Inline Code</Badge>

Input:

```text
Inline code is `cool`
```

Output:

Inline code is `cool`

<Badge tone="success">Code</Badge>

LaRecipe uses `prism.js` with code highlighting out of the box. LaRecipe supports all Prism supported languages.

Input:

\```php 

echo 'LaRecipe is beautiful';

\```

Output:

```php
echo 'LaRecipe is beautiful';
```

## Tables {#tables}

Markdown Tables automatically parsed and converted to nice looking rendered tables.

Input: 

```markdown
| # | Name   | Age |
| : |   :-   |  :  |
| 1 | Saleem | 24  |
| 2 | John   | 25  |
| 3 | Ayman  | 26  |
```

Output: 

| # | Name   | Age |
| : |   :-   |  :  |
| 1 | Saleem | 24  |
| 2 | John   | 25  |
| 3 | Ayman  | 26  |

## Alarms {#alarms}

LaRecipe provides beautiful looking alerts parsed directly from Markdown with extra sugar, all that you have to do is to prepend Markdown blockquote with `{type}` of your alert.

The supported alarms types are: **primary**, **success**, **info**, **danger**, **warning**.

Input: 

```text
> {danger} Here is an example of danger alarm message
```

Output:

> [!CAUTION]
> Here is an example of danger colored alarm

You are also able to use font-awesome icons in alarms.

Input: 

```text
> {danger.fa-close} Here is an example of a font awesome icon
```

Output:

> [!CAUTION]
> Here is an example of a font awesome icon

## Table of Content {#toc}

By default, LaRecipe is considering the first `ul` element in your docs file as the table of content and it will appear on the top-right corner of your documentation for quick navigation.

> [!IMPORTANT]
> Each link in your table of content will be targeted to the corresponding **h2** element in your documentation file.
 
```text
- [Example](#example-link)

<a name="example-link">
## [Example](#example-link)
```
