<?php

namespace Oliverde8\PageCompose\Block;
use Oliverde8\PageCompose\UiComponent\UiComponentInterface;

/**
 * Class BlockDefinition
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\Model
 */
class BlockDefinition implements BlockDefinitionInterface
{
    /** @var string */
    protected $key;

    /** @var string */
    protected $uiComponent;

    /** @var string */
    protected $parent;

    /** @var BlockDefinitionInterface[] */
    protected $subBlocks;

    /** @var array */
    protected $configuration;

    /** @var array */
    protected $globalConfiguration;

    /** @var array */
    protected $data;

    /**
     * BlockDefinition constructor.
     *
     * @param UiComponentInterface $uiComponent
     * @param string $key
     * @param string $parent
     * @param BlockDefinitionInterface[] $subBlocks
     * @param array $configuration
     * @param array $globalConfiguration
     */
    public function __construct(
        string $key,
        string $uiComponent,
        $parent,
        array $subBlocks,
        array $configuration,
        array $globalConfiguration)
    {
        $this->uiComponent = $uiComponent;
        $this->key = $key;
        $this->parent = $parent;
        $this->subBlocks = $subBlocks;
        $this->configuration = $configuration;
        $this->globalConfiguration = $globalConfiguration;
    }


    /**
     * @return string Get unique key defining the interfaces.
     */
    public function getUniqueKey(): string
    {
        return $this->key;
    }

    /**
     * @return UiComponentInterface
     */
    public function getUiComponentName(): string
    {
        return $this->uiComponent;
    }

    /**
     * @return string
     */
    public function getParentKey(): string
    {
        return $this->parent;
    }

    /**
     * Get list of sub blocks.
     *
     * @return BlockDefinitionInterface[]
     */
    public function getSubBlocks()
    {
        return $this->subBlocks;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @return array
     */
    public function getGlobalConfiguration(): array
    {
        return $this->globalConfiguration;
    }

    /**
     * Set data to be shared between prepare & display.
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get shared data between prepare & display.
     *
     * @param $key
     *
     * @return mixed
     */
    public function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}