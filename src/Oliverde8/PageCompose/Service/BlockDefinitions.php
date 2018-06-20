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

    /** @var array List of all blocks */
    protected $blocks = null;

    /** @var array Blocks grouped per parent */
    protected $blockPerParent = [];

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
     * @param $blockKey
     * @param $globalConfig
     *
     * @return null|BlockDefinition
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getBlock($blockKey, $globalConfig)
    {
        // Check caches first.
        $cacheKey = $this->normalizeCacheKey($blockKey);
        $cachedBlock = $this->cache->get($cacheKey, null);
        if (!is_null($cachedBlock)) {
            return $cachedBlock;
        }

        // Initialize blocks only when the cache can't help us.
        $this->init();

        // Check if the block can be built.
        if (!isset($this->blocks[$blockKey])) {
            return null;
        }

        // Build the block definition object.
        $block = $this->buildBlock($blockKey, $this->blocks[$blockKey], null, $globalConfig);
        $this->cache->set($cacheKey, $block);

        return $block;
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
        $originalBlock = isset($this->blocks[$blockKey]) ? $this->blocks[$blockKey] : [];

        // TODO add some more options to have a more "intelligent" replacement of existing block configs..
        $block = $originalBlock + $block;
        $this->blocks[$blockKey] = $block;

        // Check if block was moved, remove from old parent if it's the case.
        if (isset($originalBlock['parent']) && $originalBlock['parent'] != $block['parent']) {
            unset($this->blockPerParent[$originalBlock['parent']][$blockKey]);
        }

        if (isset($block['parent'])) {
            $this->blockPerParent[$block['parent']][$blockKey] = $block;
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

        if (!isset($this->blockPerParent[$parentBlockKey])) {
            return [];
        }

        foreach ($this->blockPerParent[$parentBlockKey] as $blockKey => $block) {
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
        if (isset($block['extends']) && !empty($block['extends'])) {
            $extendedBlock = $this->blocks[$block['extends']];

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

    protected function normalizeCacheKey($blockKey)
    {
        return str_replace(['{', '}', '(', ')', '/', '\\', '@'], '_', $this->cacheSuffix . $blockKey);
    }
}