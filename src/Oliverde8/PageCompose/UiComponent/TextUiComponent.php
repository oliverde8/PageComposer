<?php

namespace Oliverde8\PageCompose\UiComponent;
use Oliverde8\PageCompose\Block\BlockDefinitionInterface;

/**
 * Class TextUiComponent
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\UiComponent
 */
class TextUiComponent extends AbstractUiComponent
{

    /**
     * Display the component.
     *
     * @param BlockDefinitionInterface $blockDefinition
     * @param array ...$args
     *
     * @return string
     */
    public function display(BlockDefinitionInterface $blockDefinition, ...$args)
    {
        return $blockDefinition->getConfiguration()['text'];
    }
}