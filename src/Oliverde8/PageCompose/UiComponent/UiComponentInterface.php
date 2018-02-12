<?php

namespace Oliverde8\PageCompose\UiComponent;

use Oliverde8\PageCompose\Block\BlockDefinitionInterface;

/**
 * Class UiComponentInterface
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\UiComponent
 */
interface UiComponentInterface
{

    /**
     * Prepare component for displaying
     *
     * @param BlockDefinitionInterface $blockDefinition
     * @param array ...$args
     *
     * @return mixed
     */
    public function prepare(BlockDefinitionInterface $blockDefinition, ...$args);

    /**
     * Display the component.
     *
     * @param BlockDefinitionInterface $blockDefinition
     * @param array ...$args
     *
     * @return string
     */
    public function display(BlockDefinitionInterface $blockDefinition,  ...$args);
}