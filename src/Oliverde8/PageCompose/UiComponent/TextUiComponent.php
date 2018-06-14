<?php

namespace Oliverde8\PageCompose\UiComponent;

use function GuzzleHttp\Promise\all;
use GuzzleHttp\Promise\PromiseInterface as GuzzlePromiseInterface;
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
     * Prepare value.
     *
     * @param BlockDefinitionInterface $blockDefinition
     * @param array ...$args
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function prepare(BlockDefinitionInterface $blockDefinition, ...$args)
    {
        $promise = parent::prepare($blockDefinition, $args);
        $configuredText = $blockDefinition->getConfiguration()['text'];

        if (is_object($configuredText) && $configuredText instanceof GuzzlePromiseInterface) {
            $promise = all([$promise, $configuredText]);
            $configuredText->then(function ($value) use ($blockDefinition) {
                $blockDefinition->setData('text', $value);
            });
        } else {
            $blockDefinition->setData('text', $configuredText);
        }

        return $promise;
    }

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
        return $blockDefinition->getData('text');
    }
}