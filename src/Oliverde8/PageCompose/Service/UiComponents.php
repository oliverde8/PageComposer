<?php

namespace Oliverde8\PageCompose\Service;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Oliverde8\PageCompose\Block\BlockDefinitionInterface;
use Oliverde8\PageCompose\UiComponent\UiComponentInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class UiComponents
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\Service
 */
class UiComponents
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $eventPrefix;

    /** @var UiComponentInterface[] */
    protected $uiComponents;

    /**
     * UiComponents constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param string $eventPrefix
     */
    public function __construct(EventDispatcherInterface $eventDispatcher = null, string $eventPrefix = 'oliverde8.page_compose.ui')
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->eventPrefix = $eventPrefix;
    }


    /**
     * Prepare a UiComponent.
     *
     * @param BlockDefinitionInterface $blockDefinition
     * @param array ...$args
     *
     * @return PromiseInterface
     */
    public function prepare(BlockDefinitionInterface $blockDefinition, ...$args)
    {
        if (!isset($this->uiComponents[$blockDefinition->getUiComponentName()])) {
            return new Promise();
        }

        $this->dispatchEvent('prepare.before', $blockDefinition);
        $promise = $this->uiComponents[$blockDefinition->getUiComponentName()]->prepare($blockDefinition, ...$args);

        $that = $this;
        if (!$promise) {
            $promise = new Promise();
        }
        $promise->then(function() use ($that, $blockDefinition) {
            $that->dispatchEvent('prepare.after', $blockDefinition);
        });

        return $promise;
    }

    /**
     * Display a block.
     *
     * @param BlockDefinitionInterface $blockDefinition
     * @param array ...$args
     *
     * @return string
     */
    public function display(BlockDefinitionInterface $blockDefinition, ...$args)
    {
        if (!isset($this->uiComponents[$blockDefinition->getUiComponentName()])) {
            return null;
        }

        $this->dispatchEvent('display.before', $blockDefinition);
        $content = $this->uiComponents[$blockDefinition->getUiComponentName()]->display($blockDefinition, ...$args);
        $this->dispatchEvent('display.after', $blockDefinition);

        return $content;
    }

    /**
     * Register a ui component.
     *
     * @param string $name
     * @param UiComponentInterface $uiComponent
     */
    public function registerUiComponent($name, UiComponentInterface $uiComponent)
    {
        $this->uiComponents[$name] = $uiComponent;
    }

    /**
     * Dispatch event if possible.
     *
     * @param $name
     * @param BlockDefinitionInterface $blockDefinition
     */
    protected function dispatchEvent($name, BlockDefinitionInterface $blockDefinition)
    {
        if (is_null($this->eventDispatcher)) {
            return;
        }

        $event = new GenericEvent($blockDefinition);
        $this->eventDispatcher->dispatch($this->eventPrefix . $name, $event);
    }
}