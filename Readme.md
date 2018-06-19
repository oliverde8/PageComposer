# Page Compose 

Page compose is a library that allows to compose a page with configuration. This allows multiple components 
to override the content of a page. 

Page compose also supports promises, which will allow you to improve the rendering time of your pages.

The system was inspired by the Layout's of Magento & from the form compose of Akeneo. 
It is designed to endup being used with eXpansion the Maniaplanet server controller.

[![Build Status](https://travis-ci.org/oliverde8/PageComposer.svg?branch=master)](https://travis-ci.org/oliverde8/PageComposer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oliverde8/PageComposer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oliverde8/PageComposer/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/oliverde8/PageComposer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/oliverde8/PageComposer/?branch=master)

## Why

### Extensibility

If you start a symfony project using twig and have multiple bundles overriding your templates it's is 
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
to have easily overridable interfaces. This first version is designed for eXpansion which doesen't uses html code. 

### Decoupling

Today's websites are more and more complicated, one page contains informations that might come from various sources. 
Having a single controller manage the majority of the data displayed in a page is not an option. 

Page compose allows this to be splitted, each block being self sufficient. 

### Performance

It is hard to imagine today a website that does not require some a call to an API. These calls can be costly and slow 
down a website. 

These http calls can be done using guzzle promises asynchronously during the `prepare` phase of a block. 

Using the promises other http calls can also be run asynchronously to improve performance.

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
component displays a html list.

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
    'myPage/content/element2' => [ // A second element like the first one.
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
Before displaying the page we must first prepare the page using promises.

```php
<?php

$promises = [];
foreach ($pageBlocks as $blockDefinition) {
    $promises[] = $uiComponents->prepare($blockDefinition, []);
}
```

We now need to wait for all the promises to resolve.

```php
<?php

$promise = \GuzzleHttp\Promise\all($promises);
$promise->wait();
```

**Step 4**

We can now display the content of the page.

```php
<?php 

foreach ($blockDefinitions->getPageBlocks('myPage', []) as $block) {
    echo $uiComponents->display($block);
}
```

As you can see display doesen't display it retuns a string. That's because the system intends to be generic. The way
sub children should be handle depends on the usage. My primary usage being to append various objects for eXpansion.

## What else 

Check our [examples](./exemples).

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
    'abstract/content/sub' => [
        'component' => 'text',
        'parent'  => 'abstract/content',
        'config' => [          
            'text' => 'my Text'
        ],
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

When you extend you also get the sub elements of the element you extend, in this case `mySecondPage/content` 
has `abstract/content/sub` as a sub block.

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

### Defining aliases. 

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

    ],
    'myPage/content/element1' => [
        'alias' => "element1",
        'parent' => 'myPage/content',
        'component' => 'text',       
        'config' => [
            'text' => 'My Test Content from parent'
        ]
    ],
    'myPage/content/element2' => [
        'alias' => "element1",
        'parent' => 'myPage/content',
        'component' => 'text',
        'config' => [
            'text' => 'My Test Content from element'
        ]
    ]
];
````

This can be practical if the parent elements display needs to be aware of it's children. 

**Exemple :** You have a list of products, in the parent element, and would like to display them. You can call 
the children element whose alias is `thumbnail` and display is as many times as necessary. 

```php
<?php
foreach ($products as $product) {
    $this->uiComponents->display($blockDefinition->getSubBlocks()['thumbnail'], $product);
}
```

### Sorting elements

You can change the order of the elements. 

> TODO not coded yet. It's not priority as for our usage the sorting will be probably 
> done when reading the configuration files. 

## FAQ

**Why isn't there a yml or xml reader ?**
It would indeed be much nicer to be able to configure our page structure in a config file. Even better if it was
a xml file where can can really have a real "tree" view of our page. We do not implement this as it would be easier
to have this on the application layer, in a symfony bundle for example. 

## TODO

* [ ] Test some more with complex layouts.
* [ ] We are missing priorities for different components in the same parent. 
* [ ] Have a service for handling HTML display as usage at the moment is a bit complicated.