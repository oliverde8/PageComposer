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

        $blockDefinition1 = new BlockDefinition('test', 'text', 'test', [], [], []);
        $blockDefinition2 = new BlockDefinition('test', 'text2', 'test', [$blockDefinition1], [], []);

        $this->uiComponents->registerUiComponent('text', $uiComponent1);
        $this->uiComponents->registerUiComponent('text2', $uiComponent2);

        $this->uiComponents->prepare($blockDefinition2  , 'toto', 'tata');
    }

    public function testDisplay()
    {
        $blockDefinition = new BlockDefinition('test', 'text', 'test', [], ['text' => 'Test text'], []);

        $uiComponent = new TextUiComponent($this->uiComponents);

        $this->uiComponents->registerUiComponent('text', $uiComponent);
        $this->assertEquals('Test text', $this->uiComponents->display($blockDefinition));
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