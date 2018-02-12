<?php

namespace Oliverde8\PageCompose\Service;

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
                $this->cache->set($this->cacheSuffix . $pageKey, serialize($blocks));
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
        foreach ($this->buildFactories as $buildFactory) {
            foreach ($buildFactory() as $blocKey => $block) {
                $this->registerBlock($blocKey, $block);
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

        // TODO validate minimal information!
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

        $subBlocks = [];
        foreach ($this->blocks[$parentBlockKey] as $blockKey => $block) {
            $subBlocks[$blockKey] = $this->buildBlock($blockKey, $block, $parentBlockKey, $globalConfig);
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
        $config = isset($block['config']) ? $block['config'] : [];
        $config = $parentGlobalConfig + $config;

        // Merge both configs.
        $globalConfig = isset($block['globalConfig']) ? $block['globalConfig'] : [];
        $globalConfig = $parentGlobalConfig + $globalConfig;

        // Allow definition to extend another definition.
        if (isset($block['extends'])) {
            $extendedBlock = $this->abstractblocks[$block]['extends'];
            $config = (isset($extendedBlock['config']) ? $extendedBlock['config'] : []) + $config;
            $globalConfig = (isset($extendedBlock['globalConfig']) ? $extendedBlock['globalConfig'] : []) + $globalConfig;

            if (isset($extendedBlock['component']) && !isset($block['component'])) {
                $block['component'] = $extendedBlock['component'];
            }
        }

        // fetch sub blocks.
        $subBlocks = $this->buildBlocks($blockKey, $globalConfig);

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