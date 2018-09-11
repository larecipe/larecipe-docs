# Vue Customization

Thanks to VueJs, we can also use dynamic binding to change the behavior of the current Vue components. Here you can find all the props and directives that you can use in order to customize the components' behavior.

---

- [Cards](#cards)
- [Buttons](#buttons)
- [Badges](#badges)
- [Progress](#progress)

<a name="cards"></a>
## Cards

`default option`

|Prop Name|Options|
|:-|:-|
|type|primary, success, danger, warning, info|
|gradient|primary, success, danger, warning, info|
|hover|true, `false`|
|shadow|true, `false`|



<a name="buttons"></a>
## Buttons

|Prop Name|Options|
|:-|:-|
|type|<larecipe-button>default</larecipe-button> <larecipe-button type="primary">primary</larecipe-button> <larecipe-button type="success">success</larecipe-button> <larecipe-button type="danger">danger</larecipe-button> <larecipe-button type="warning">warning</larecipe-button> <larecipe-button type="info">info</larecipe-button>|
|outline|<larecipe-button :outline="true">outline</larecipe-button> <larecipe-button type="primary" :outline="true">primary</larecipe-button> <larecipe-button type="success" :outline="true">success</larecipe-button> <larecipe-button type="danger" :outline="true">danger</larecipe-button> <larecipe-button type="warning" :outline="true">warning</larecipe-button> <larecipe-button type="info" :outline="true">info</larecipe-button>|
|size|<larecipe-button size="sm">sm</larecipe-button> <larecipe-button size="lg">lg</larecipe-button>|



<a name="badges"></a>
## Badges

`default option`

|Prop Name|Options|
|:-|:-|
|type|<larecipe-badge>default</larecipe-badge> <larecipe-badge type="primary">primary</larecipe-badge> <larecipe-badge type="success">success</larecipe-badge> <larecipe-badge type="danger">danger</larecipe-badge> <larecipe-badge type="warning">warning</larecipe-badge> <larecipe-badge type="info">info</larecipe-badge>|
|rounded|<larecipe-badge rounded>default</larecipe-badge> <larecipe-badge rounded type="primary">primary</larecipe-badge> <larecipe-badge rounded type="success">success</larecipe-badge> <larecipe-badge rounded type="danger">danger</larecipe-badge> <larecipe-badge rounded type="warning">warning</larecipe-badge> <larecipe-badge rounded type="info">info</larecipe-badge>|

<a name="progress"></a>
## Progress

`default option`

> {info} `animated` works only when `striped` option enabled


|Prop Name|Options|
|:-|:-|
|type|`default`, primary, success, danger, warning, info|
|striped|true, `false`|
|animated|true, `false`|
|value|`0`|