<?php

namespace Oliverde8\PageCompose\UiComponent\Html;
use Oliverde8\PageCompose\Block\BlockDefinitionInterface;
use Oliverde8\PageCompose\Service\UiComponents;
use Oliverde8\PageCompose\UiComponent\AbstractUiComponent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class AbstractHtml
 *
 * @author    de Cramer Oliver<oliverde8@gmail.com>
 * @copyright 2018 Oliverde8
 * @package Oliverde8\PageCompose\UiComponent\Html
 */
abstract class AbstractHtml extends AbstractUiComponent
{
    /** @var string */
    protected $properties = [];

    /** @var PropertyAccessor  */
    protected $arrayAccess;

    /**
     * HtmlList constructor.
     *
     * @param array $properties
     */
    public function __construct(UiComponents $uiComponent, array $properties = ['class' => 'class', 'id' => 'id'])
    {
        parent::__construct($uiComponent);

        $this->properties = $properties;
        $this->arrayAccess = PropertyAccess::createPropertyAccessor();
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

    }

    /**
     * Build HTML properties
     *
     * @param array  $blockConfiguration
     * @param string $suffix
     *
     * @return string
     */
    protected function buildProperties($blockConfiguration, $suffix)
    {
        $html = '';
        foreach ($this->properties as $property => $propertyConfig) {
            $html .= htmlspecialchars($property) . '="';
            $html .= $this->getProperyHtmlValue($blockConfiguration, "[$propertyConfig]$suffix") . '" ';
        }

        return $html;
    }

    /**
     * Build html value for property
     *
     * @param $blockConfiguration
     * @param $property
     *
     * @return string
     */
    protected function getProperyHtmlValue($blockConfiguration, $property)
    {
        if (!$this->arrayAccess->getValue($blockConfiguration, $property)) {
            return '';
        }

        return implode(
            ' ',
            array_map(
                function ($class) { return htmlspecialchars($class); },
                $this->arrayAccess->getValue($blockConfiguration, $property)
            )
        );
    }
}