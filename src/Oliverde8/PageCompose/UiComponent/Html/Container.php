<?php

namespace Oliverde8\PageCompose\UiComponent\Html;

use Oliverde8\PageCompose\Block\BlockDefinitionInterface;

/**
 * Class Container
 *
 * @author    de Cramer Oliver<oiverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\UiComponent\Html
 */
class Container extends AbstractHtml
{
    public function display(BlockDefinitionInterface $blockDefinition, ...$args)
    {
        $html = parent::display($blockDefinition, ...$args);
        foreach ($blockDefinition->getSubBlocks() as $block) {
            $html .= $this->uiComponents->display($block, ...$args);
        }

        return $html;
    }
}
