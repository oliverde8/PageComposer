<?php

namespace Oliverde8\PageCompose\Block;

use Oliverde8\PageCompose\UiComponent\UiComponentInterface;

/**
 * Class BlockDefinitionInterface
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\Model
 */
interface BlockDefinitionInterface
{
    /**
     * @return string Get unique key defining the interfaces.
     */
    public function getUniqueKey() : string;

    /**
     * @return string
     */
    public function getUiComponentName();

    /**
     * @return string Get unique key defining the parent.
     */
    public function getParentKey() : string;

    /**
     * Get list of sub blocks.
     *
     * @return BlockDefinitionInterface[]
     */
    public function getSubBlocks();

    /**
     * Get page configuration.
     *
     * @return array
     */
    public function getConfiguration();

    /**
     * Get global configuration to be applied to all sub blocks.
     *
     * @return array
     */
    public function getGlobalConfiguration();
}