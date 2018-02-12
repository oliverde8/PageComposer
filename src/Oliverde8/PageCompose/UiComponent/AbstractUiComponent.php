<?php

namespace Oliverde8\PageCompose\UiComponent;

use Oliverde8\PageCompose\Block\BlockDefinitionInterface;
use Oliverde8\PageCompose\Service\UiComponents;

/**
 * Class SimpleParentComponent
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\UiComponent
 */
abstract class AbstractUiComponent implements UiComponentInterface
{
    /** @var UiComponents */
    protected $uiComponents;

    /**
     * AbstractUiComponent constructor.
     *
     * @param UiComponents $uiComponents
     */
    public function __construct(UiComponents $uiComponents)
    {
        $this->uiComponents = $uiComponents;
    }


    /**
     * @inheritdoc
     */
    public function prepare(BlockDefinitionInterface $blockDefinition, ...$args)
    {
        foreach ($blockDefinition->getSubBlocks() as $block) {
            $this->uiComponents->prepare($block, ...$args);
        }
    }
}
