<?php
/**
 * File test.php
 *
 * @author    de Cramer Oliver<oldec@smile.fr>
 * @copyright 2018 Smile
 */

require_once '../../vendor/autoload.php';

$autoLoader = new \Composer\Autoload\ClassLoader();
$autoLoader->addPsr4('', [realpath(__DIR__ . DIRECTORY_SEPARATOR . 'src')]);
$autoLoader->register(true);

/**
 * Defining our layout.
 */
$blockDefinitions = new \Oliverde8\PageCompose\Service\BlockDefinitions(
    new \Symfony\Component\Cache\Simple\ArrayCache(),
    'page_compose',
    [
        function() {
            return [
                'myPage/list' => [
                    'parent' => 'myPage',
                    'component' => 'list',
                    'config' => [
                        'class' => ['ul' => ['super_parent_class'], 'li' => ['super_li_class']]
                    ]
                ],
                'myPage/test/element1' => [
                    'parent' => 'myPage/list',
                    'component' => 'text',
                    'config' => [
                        'text' => 'My Test Content 1'
                    ]
                ],
                'myPage/test/element2' => [
                    'parent' => 'myPage/list',
                    'component' => 'text',
                    'config' => [
                        'text' => 'My Test Content 2'
                    ]
                ]
            ];
        }
    ]
);

/**
 * Defining the ui comonents
 */
$uiComponents = new \Oliverde8\PageCompose\Service\UiComponents();
$uiComponents->registerUiComponent('list', new \Oliverde8\PageCompose\UiComponent\Html\HtmlList($uiComponents));
$uiComponents->registerUiComponent('text', new \Oliverde8\PageCompose\UiComponent\TextUiComponent($uiComponents));



echo "\n\n";