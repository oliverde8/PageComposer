<?php
/**
 * File guzzle_http.php
 *
 * In this test one of our blocks will be doing a http query to fetch it's content.
 *
 * @author    de Cramer Oliver<oldec@smile.fr>
 * @copyright 2018 Smile
 */

require_once __DIR__ . '/../vendor/autoload.php';

$autoLoader = new \Composer\Autoload\ClassLoader();
$autoLoader->addPsr4('', [realpath(__DIR__ . DIRECTORY_SEPARATOR . 'src')]);
$autoLoader->register(true);

$client = new \GuzzleHttp\Client(['base_uri' => 'http://www.mocky.io/']);
/**
 * Defining our layout.
 */
$blockDefinitions = new \Oliverde8\PageCompose\Service\BlockDefinitions(
    new \Symfony\Component\Cache\Simple\ArrayCache(),
    'page_compose',
    [
        function() use ($client) {
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
                        'text' => $client->getAsync('v2/5b225f282e00006500e31672')->then(function (\GuzzleHttp\Psr7\Response $value) {
                            echo "Http actually finishes now!\n";
                            return $value->getBody();
                        }),
                    ]
                ],
                'myPage/test/element3' => [
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


$pageBlocks = $blockDefinitions->getPageBlocks('myPage', []);
$promises = [];

foreach ($pageBlocks as $blockDefinition) {
    $promises[] = $uiComponents->prepare($blockDefinition, []);
}

echo "Http responses are not ready, but they are running\n";

$promise = \GuzzleHttp\Promise\all($promises);
$promise->wait();

echo "Blocks have been prepared ready to be displayed\n\n";
echo "Content : \n\n";

foreach ($pageBlocks as $blockDefinition) {
    echo $uiComponents->display($blockDefinition, []);
}

echo "\n\n";