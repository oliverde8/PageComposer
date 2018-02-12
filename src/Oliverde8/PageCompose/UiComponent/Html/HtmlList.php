<?php

namespace Oliverde8\PageCompose\UiComponent\Html;

use Oliverde8\PageCompose\Block\BlockDefinitionInterface;
use Oliverde8\PageCompose\Service\UiComponents;
use Oliverde8\PageCompose\UiComponent\AbstractUiComponent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class HtmlList
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\UiComponent\Html
 */
class HtmlList extends AbstractHtml
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
        $html = parent::display($blockDefinition, ...$args);
        $html .= '<ul ';
        $html .= $this->buildProperties($blockDefinition->getConfiguration(), '[ul]');
        $html .= '>';

        foreach ($blockDefinition->getSubBlocks() as $block) {
            $html .= '<li ';
            $html .= $this->buildProperties($blockDefinition->getConfiguration(), '[li]');
            $html .= '>';

            $html .= $this->uiComponents->display($block, ...$args);

            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}