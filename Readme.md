# Page Compose 

Page compose is a library that allows to compose a page with configuration. This allows multiple components 
to overide the content of a page. 

The system was inspired by the Layout's of Magento & from the form compose of Akeneo. 
It is designed to endup being used with eXpansion the Maniaplanet server controller.

## Why

If you start a symfony project using twig and have multiple bundles overriding your your templates it's is 
going to become a nightmare to manage. 

For example, you wish to display a certain content on the left sidebar of your page. The CmsBundle displays 
the last articles, but you would like to have the Github bundle display your latest github activity just after. 
Todo this you will need to have a MyTotoBundle that displays the sidebar then fetches content from the other 2 
bundles.

So if you intend to do a fully customizable system, you are going to end up having 
to adapt various bundles to make them fit. 

The idea of this library is to offer a common ground for configuration based page layouts in order to compose a page. 
A Symfony bundle that will be coded later should make it easier to understand. 

This library is not meant to be used on simple websites, it's meant to be used for CMS and other softwares that needs
to have easily overridable interfaces.

## Simple usage exemple 

**Step 1**
First we need to define the various Ui Components available. Those are in charge of the display.

```php
<?php
$uiComponents = new \Oliverde8\PageCompose\Service\UiComponents();
$uiComponents->registerUiComponent('list', new \Oliverde8\PageCompose\UiComponent\Html\HtmlList($uiComponents));
$uiComponents->registerUiComponent('text', new \Oliverde8\PageCompose\UiComponent\TextUiComponent($uiComponents));
```

Here we have defined a list and a text component. The text component can display a simple text, while the list 
component displayes a html list.

**Step 2** 
Let's define the content of `myPage`

```php
<?php
$layout = [
    'myPage/content' => [
        'parent' => 'myPage',  // The name of our pages.
        'component' => 'list', // The sub blocks will be displayed using the list ui component.
        'config' => [          // Different ui elements can use different configs
            'class' => ['ul' => ['super_parent_class'], 'li' => ['super_li_class']]
        ]
    ],
    'myPage/content/element1' => [
        'parent' => 'myPage/content', // Sub element of the content of the page.
        'component' => 'text',        // We want to display a simple text
        'config' => [
            'text' => 'My Test Content 1' // The text to display.
        ]
    ],
    'myPage/content/element2' => [ // A second elemnt like the first one.
        'parent' => 'myPage/content',
        'component' => 'text',
        'config' => [
            'text' => 'My Test Content 2'
        ]
    ]
];
```

Now let's create a our Block definitions. 

```php
<?php
new \Oliverde8\PageCompose\Service\BlockDefinitions(
    new \Symfony\Component\Cache\Simple\ArrayCache(),
    'page_compose',
    [function() use ($layout) { return $layout; }]
);
```

**Step 3**
We can now display our page. 

```php
<?php
foreach ($blockDefinitions->getPageBlocks('myPage', []) as $block) {
    echo $uiComponents->display($block);
}
```

## What else 

### Extending blocks

Will allow to copy all information of another block beside it's parent to the new block. 

**Exemple:**
```php
<?php
$layout = [
    'abstract/content' => [
        'component' => 'list',
        'config' => [          
            'class' => ['ul' => ['super_parent_class'], 'li' => ['super_li_class']]
        ]
    ],
    'myPage/content' => [
        'parent' => 'myPage',  
        'extends' => 'abstract/content'
    ],
    'myPage/content/element1' => [
        // ...
    ],
    //...
    'mySecondPage/content' => [
        'parent' => 'myPage',  
        'extends' => 'abstract/content'
    ],
    //...
];
```

### Global configs. 

You might wish to set some options globally at the parent level for it to be the same for all childrens. 

**Exemple**
```php
<?php
$layout = [
    'myPage/content' => [
        'parent' => 'myPage',  
        'component' => 'list', 
        'config' => [          
            'class' => ['ul' => ['super_parent_class'], 'li' => ['super_li_class']]
        ],
        'globalConfig' => [
            'text' => 'My Test Content from parent'
        ]
    ],
    'myPage/content/element1' => [
        'parent' => 'myPage/content', // Sub element of the content of the page.
        'component' => 'text',        // We want to display a simple text
                                      // No configs this will display "My Test Content from parent"
    ],
    'myPage/content/element2' => [
        'parent' => 'myPage/content',
        'component' => 'text',
        'config' => [
            'text' => 'My Test Content from element'
        ]
    ]
];
````

Global configs can also be set when calling for the display.

**Exemple**
```php
<?php
foreach ($blockDefinitions->getPageBlocks('myPage', ['text' => 'My Global text']) as $block) {
    echo $uiComponents->display($block);
}
```

### Sorting elements

You can change the order of the elements. 

// TODO not coded yet. 

## TODO

* [ ] We are missing priorities for different components in the same parent. 
* [ ] The config merging of blocks is surely not correct, needs to be checked. 
* [ ] Improve memory : Currently all block definitions are loaded at once, should be loaded as needed. 
* [ ] Improve memory : Currently all ui Components are created from start, should use callables like for block definitions.
* [ ] Test, Test & Some unit tests. 