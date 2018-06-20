<?php
/**
 * File UiComponentsTest.php
 *
 * @author    de Cramer Oliver<oldec@smile.fr>
 * @copyright 2018 Smile
 */

namespace Oliverde8\PageCompose\Tests\Service;

use Oliverde8\PageCompose\Block\BlockDefinition;
use Oliverde8\PageCompose\Service\UiComponents;
use Oliverde8\PageCompose\UiComponent\AbstractUiComponent;
use Oliverde8\PageCompose\UiComponent\TextUiComponent;
use Oliverde8\PageCompose\UiComponent\UiComponentInterface;
use PHPUnit\Framework\TestCase;


class UiComponentsTest extends TestCase
{
    /** @var UiComponents */
    protected $uiComponents;

    protected function setUp()
    {
        parent::setUp();

        $this->uiComponents = new UiComponents();
    }

    /**
     * Test that prepare is well called on the ui interface of a block definition.
     */
    public function testPrepare()
    {
        $blockDefinition = new BlockDefinition('test', 'text', 'test', [], [], []);

        $uiComponent = $this->getMockBuilder(UiComponentInterface::class)->getMock();
        $uiComponent->expects($this->once())->method('prepare')->with($blockDefinition, 'toto', 'tata');

        $this->uiComponents->registerUiComponent('text', $uiComponent);
        $this->uiComponents->prepare($blockDefinition, 'toto', 'tata');
    }

    /**
     * Test that if called for invalid definition nothing happens.
     */
    public function testPrepareInvalid()
    {
        $blockDefinition = new BlockDefinition('test', 'text', 'test', [], [], []);

        $uiComponent = $this->getMockBuilder(UiComponentInterface::class)->getMock();
        $uiComponent->expects($this->never())->method('prepare');

        $this->uiComponents->registerUiComponent('toto', $uiComponent);
        $this->uiComponents->prepare($blockDefinition, 'toto', 'tata');
    }

    /**
     * Prepare should prepare all children as well
     */
    public function testPrepareRecursive()
    {
        $uiComponent1 = $this->getMockBuilder(AbstractUiComponent::class)->setConstructorArgs([$this->uiComponents])->getMock();
        $uiComponent1->expects($this->once())->method('prepare');

        $uiComponent2 = new TextUiComponent($this->uiComponents);

        $blockDefinition1 = new BlockDefinition('test', 'text', 'test', [], ['text' => ''], []);
        $blockDefinition2 = new BlockDefinition('test', 'text2', 'test', [$blockDefinition1], ['text' => ''], []);

        $this->uiComponents->registerUiComponent('text', $uiComponent1);
        $this->uiComponents->registerUiComponent('text2', $uiComponent2);

        $this->uiComponents->prepare($blockDefinition2  , 'toto', 'tata');
    }

    public function testDisplay()
    {
        $uiComponent = new TextUiComponent($this->uiComponents);
        $this->uiComponents->registerUiComponent('text', $uiComponent);

        $blockDefinition = new BlockDefinition('test', 'text', 'test', [], ['text' => 'Test text'], []);

        // Prepare everything and wait for it.
        $this->uiComponents->prepare($blockDefinition, [])->wait();

        $this->assertEquals('Test text', $this->uiComponents->display($blockDefinition));
    }

    public function testWithPromise()
    {
        $client = new \GuzzleHttp\Client(['base_uri' => 'http://www.mocky.io/']);
        $httpPromise = $client->getAsync('v2/5b225f282e00006500e31672')->then(function (\GuzzleHttp\Psr7\Response $value) {
            return $value->getBody();
        });

        $uiComponent = new TextUiComponent($this->uiComponents);
        $this->uiComponents->registerUiComponent('text', $uiComponent);

        $blockDefinition = new BlockDefinition('test', 'text', 'test', [], ['text' => $httpPromise], []);

        // Prepare everything and wait for it.
        $this->uiComponents->prepare($blockDefinition, [])->wait();

        $this->assertEquals('This is my http test', $this->uiComponents->display($blockDefinition));
    }
    
    public function testDisplayInvalid()
    {
        $blockDefinition = new BlockDefinition('test', 'text', 'test', [], [], []);

        $uiComponent = $this->getMockBuilder(UiComponentInterface::class)->getMock();
        $uiComponent->expects($this->never())->method('display');

        $this->uiComponents->registerUiComponent('toto', $uiComponent);
        $this->uiComponents->display($blockDefinition, 'toto', 'tata');
    }
}
