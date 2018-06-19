<?php

namespace Oliverde8\PageCompose\Service;

use oliverde8\AssociativeArraySimplified\AssociativeArray;
use Oliverde8\PageCompose\Block\BlockDefinition;
use Oliverde8\PageCompose\Block\BlockDefinitionInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class BlockDefinitions
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\Service
 */
class BlockDefinitions
{
    /** @var CacheInterface */
    protected $cache;

    /** @var string  */
    protected $cacheSuffix = 'page.definitions.';

    /** @var callable[] */
    protected $buildFactories;

    /** @var BlockDefinitionInterface[][] */
    protected $blockDefinitions = [];

    /** @var array */
    protected $blocks = null;

    /** @var array */
    protected $abstractblocks;

    /**
     * BlockDefinitions constructor.
     *
     * @param CacheInterface $cache
     * @param string $cacheSuffix
     * @param callable[] $buildFactories
     */
    public function __construct(CacheInterface $cache, string $cacheSuffix, array $buildFactories)
    {
        $this->cache = $cache;
        $this->cacheSuffix = $cacheSuffix;
        $this->buildFactories = $buildFactories;
    }


    /**
     * Get sub blocks of a page
     *
     * @param $pageKey
     * @param $globalConfig
     *
     * @return BlockDefinitionInterface[]
     * @throws
     */
    public function getPageBlocks($pageKey, $globalConfig)
    {
        if (!array_key_exists($pageKey, $this->blockDefinitions)) {
            $blocks = $this->cache->get($this->cacheSuffix . $pageKey, null);
            if (!is_null($blocks)) {
                $blocks = unserialize($blocks);
            } else {
                $blocks = $this->buildBlocks($pageKey, $globalConfig);
                $this->cache->set($this->cacheSuffix . $pageKey, $blocks);
            }

            $this->blockDefinitions[$pageKey] = $blocks;
        }

        return $this->blockDefinitions[$pageKey];
    }

    /**
     * Initialize all block information. Not always necessery if caches are sufficient.
     */
    protected function init()
    {
        if (!is_null($this->blocks)) {
            return;
        }

        $this->blocks = [];
        foreach ($this->buildFactories as $key => $buildFactory) {
            if (is_callable($buildFactory)) {
                foreach ($buildFactory() as $blocKey => $block) {
                    $this->registerBlock($blocKey, $block);
                }
            }
            else {
                $this->registerBlock($key, $buildFactory);
            }
        }
    }

    /**
     * Register a block
     *
     * @param string $blockKey The unique key of the block to register.
     * @param array  $block    Data of the block definition.
     */
    protected function registerBlock($blockKey, $block)
    {
        $originalBloc = isset($this->blocks[$blockKey]) ? $this->blocks[$blockKey] : [];

        $block = $originalBloc + $block;

        if (isset($block['parent'])) {
            $this->blocks[$block['parent']][$blockKey] = $block;
        } else {
            $this->abstractblocks[$blockKey] = $block;
        }
    }

    /**
     * Get all sub blocks.
     *
     * @param $parentBlockKey
     * @param $globalConfig
     *
     * @return array
     */
    protected function buildBlocks($parentBlockKey, $globalConfig)
    {
        $this->init();

        if (!isset($this->blocks[$parentBlockKey])) {
            return [];
        }

        // Fetch blocks for extended block first.
        $subBlocks = [];
        if (isset($this->abstractblocks[$parentBlockKey]) && isset($this->abstractblocks[$parentBlockKey]['extends'])) {
            $subBlocks = $this->buildBlocks($this->abstractblocks[$parentBlockKey]['extends'], $globalConfig);
        }

        foreach ($this->blocks[$parentBlockKey] as $blockKey => $block) {
            $alias = AssociativeArray::getFromKey($block, 'alias', $blockKey);
            $subBlocks[$alias] = $this->buildBlock($blockKey, $block, $parentBlockKey, $globalConfig);
        }

        return $subBlocks;
    }

    /**
     * Build a certain block.
     *
     * @param $blockKey
     * @param $block
     * @param $parentBlocKey
     * @param $parentGlobalConfig
     *
     * @return BlockDefinition
     */
    protected function buildBlock($blockKey, $block, $parentBlocKey, $parentGlobalConfig)
    {
        // Merge parent config into the main config.
        $config = AssociativeArray::getFromKey($block, 'config', []);
        $config = $parentGlobalConfig + $config;

        // Merge both configs.
        $globalConfig = AssociativeArray::getFromKey($block, 'globalConfig', []);
        $globalConfig = $parentGlobalConfig + $globalConfig;

        // Define empty list of sub blocks.
        $subBlocks = [];

        // Allow definition to extend another definition.
        if (isset($block['extends'])) {
            $extendedBlock = $this->abstractblocks[$block['extends']];

            $config = array_merge_recursive(AssociativeArray::getFromKey($extendedBlock, 'config', []), $config);
            $globalConfig = array_merge_recursive(AssociativeArray::getFromKey($extendedBlock, 'globalConfig', []), $globalConfig);
            $block['component'] = AssociativeArray::getFromKey($block, 'component', $extendedBlock['component']);

            $subBlocks = $this->buildBlocks($block['extends'], $globalConfig);
        }

        // fetch sub blocks.
        $subBlocks = $subBlocks + $this->buildBlocks($blockKey, $globalConfig);

        // Fetch block definition.
        return new BlockDefinition(
            $blockKey,
            $block['component'],
            $parentBlocKey,
            $subBlocks,
            $config,
            $globalConfig
        );
    }
}