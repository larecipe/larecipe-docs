---
title: 'Vue Components Support'
description: "Because LaRecipe renders the documentations in the back-end of Laravel application, it's pretty cool to leverage custom VueJs components."
---

# Vue Components Support

## Built-in Components

LaRecipe provides a bunch of amazing looking UI Vue based components.

> [!WARNING]
> If you don't like the idea of mixing Vue components with Markdown, remember that this feature is optional. 

## Cards {#cards}

Input:

```html
<larecipe-card shadow>
    Example card
</larecipe-card>
```

Output:

<Card>
Example card
</Card>

## Buttons {#buttons}

Input:

```html
<larecipe-button radius="full">Example button</larecipe-button>
<larecipe-button type="success" radius="full">Success button</larecipe-button>
```

Output:

<Button>Default button</Button>
<Button>Success button</Button>

## Badges {#badges}

Input:

```html
<larecipe-badge type="primary" circle icon="fa fa-user"></larecipe-badge>
<larecipe-badge type="success" rounded>Holly</larecipe-badge>
<larecipe-badge type="danger" rounded>Molly</larecipe-badge>
```

Output:

<Badge tone="info">User</Badge>
<Badge tone="success">Holla</Badge>
<Badge tone="danger">Molly</Badge>

## Progress {#progress}

Input:

```html
<larecipe-progress type="success" :value="60"></larecipe-progress>
```

Output:

> [!IMPORTANT]
> You can achieve the same effect on the overview page by including a progress component inside a card.

Input:

```html
<larecipe-card>
    <larecipe-badge type="success" circle class="mr-3" icon="fa fa-heart"></larecipe-badge> BinaryTorch
    <larecipe-progress type="success" :value="100"></larecipe-progress>
</larecipe-card>
```

Output:

<Card>
BinaryTorch
</Card>
