<?php
/**
 * File TestBlockDefinitions.php
 *
 * @author    de Cramer Oliver<oldec@smile.fr>
 * @copyright 2018 Smile
 */

namespace Oliverde8\PageCompose\Tests\Service;

use Oliverde8\PageCompose\Block\BlockDefinition;
use Oliverde8\PageCompose\Block\BlockDefinitionInterface;
use Oliverde8\PageCompose\Service\BlockDefinitions;
use Oliverde8\PageCompose\Tests\DummyCache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class BlockDefinitionsTest extends TestCase
{

    /**
     * @dataProvider blockProvider
     */
    public function testGetPageBlocks($blockDefinition, $expected)
    {
        $dummyCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $dummyCache->method('get')->willReturn(null);
        $dummyCache->method('set')->willReturn(true);

        $blockDefinitions = new BlockDefinitions(
            $dummyCache,
            'mycache',
            [function() use ($blockDefinition) { return $blockDefinition; }]
        );

        $blocks = $blockDefinitions->getBlock('myPage', []);
        $this->recursiveTestExpected($blocks->getSubBlocks(), $expected);
    }

    public function testCacheUsageHit()
    {
        $blockDefinition = new BlockDefinition('test', 'test', 'test', [], [], []);

        $dummyCache = $this->getMockBuilder(CacheInterface::class)->getMock();
        $dummyCache->method('get')->with('mycache.test')->willReturn($blockDefinition);

        $blockDefinitions = new BlockDefinitions(
            $dummyCache,
            'mycache.',
            []
        );

        $this->assertEquals($blockDefinition, $blockDefinitions->getBlock('test', []));
    }

    /**
     * @param BlockDefinitionInterface[] $blocks
     * @param $expected
     */
    protected function recursiveTestExpected($blocks, $expected)
    {
        foreach ($expected as $key => $values) {
            $this->assertArrayHasKey($key, $blocks, 'Missing sub block : ' . $key);

            foreach ($values as $valueKey => $value) {
                if ($valueKey == "subs") {
                    $this->assertEquals(count($value), count($blocks[$key]->getSubBlocks()), 'Not same amount of childs');
                    $this->recursiveTestExpected($blocks[$key]->getSubBlocks(), $value);
                } else {
                    $realValue = $blocks[$key]->{"get$valueKey"}();
                    $this->assertEquals($value, $realValue, "Wrong data for $valueKey");
                }
            }
        }
    }

    public function blockProvider()
    {
        return [
            'Simple single block' => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'myPage/text' => [
                        'parent' => 'myPage',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test'
                        ]
                     ],
                ],
                [
                    'myPage/text' => [
                        'subs' => [],
                        'uniqueKey' => 'myPage/text',
                        'parentKey' => 'myPage',
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test'
                        ],
                        'globalConfiguration' => []
                    ]
                ]
            ],
            'Simple double block' => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'myPage/text1' => [
                        'parent' => 'myPage',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test1'
                        ]
                    ],
                    'myPage/text2' => [
                        'parent' => 'myPage',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test2'
                        ]
                    ],
                ],
                [
                    'myPage/text1' => [
                        'subs' => [],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test1'
                        ]
                    ],
                    'myPage/text2' => [
                        'subs' => [],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test2'
                        ]
                    ]
                ]
            ],
            "With sub blocks" => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'myPage/text1' => [
                        'parent' => 'myPage',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test1'
                        ]
                    ],
                    'myPage/text1/sub' => [
                        'parent' => 'myPage/text1',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test sub'
                        ]
                    ],
                    'myPage/text2' => [
                        'parent' => 'myPage',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test2'
                        ]
                    ],
                ],
                [
                    'myPage/text1' => [
                        'subs' => [
                            'myPage/text1/sub' => [
                                'UiComponentName' => 'text',
                                'configuration' => [
                                    'text' => 'test sub'
                                ]
                            ],
                        ],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test1'
                        ]
                    ],
                    'myPage/text2' => [
                        'subs' => [],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test2'
                        ]
                    ]
                ]
            ],
            "Aliases" => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'myPage/text' => [
                        'parent' => 'myPage',
                        'alias' => 'myText',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test'
                        ]
                    ],
                ],
                [
                    'myText' => [ // So my text instead of myPage/text due to the alias.
                        'subs' => [],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test'
                        ]
                    ]
                ]
            ],
            "Global config" => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'myPage/text' => [
                        'parent' => 'myPage',
                        'component' => 'text',
                        'globalConfig' => [
                            'text' => 'test'
                        ]
                    ],
                    'myPage/text/sub' => [
                        'parent' => 'myPage/text',
                        'component' => 'text',
                    ],
                ],
                [
                    'myPage/text' => [
                        'subs' => [
                            'myPage/text/sub' => [
                                'UiComponentName' => 'text',
                                'configuration' => [
                                    'text' => 'test'
                                ]
                            ]
                        ],
                        'UiComponentName' => 'text',
                        'configuration' => []
                    ]
                ]
            ],
            "Extend without sub" => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'abstract/text' => [
                        'alias' => 'myText',
                        'component' => 'text',
                        'config' => [
                            'text' => 'test'
                        ]
                    ],
                    'myPage/text' => [
                        'parent' => 'myPage',
                        'extends' => 'abstract/text',
                    ],
                ],
                [
                    'myPage/text' => [
                        'subs' => [],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test'
                        ]
                    ]
                ]
            ],
            "Extend with sub" => [
                [
                    'myPage' => [
                        'component' => 'text',
                        'config' => ['text' => 'test']
                    ],
                    'abstract/text' => [
                        'component' => 'text',
                        'config' => [
                            'text' => 'test'
                        ]
                    ],
                    'abstract/text/sub' => [
                        'parent' => 'abstract/text',
                        'component' => 'text',
                    ],
                    'myPage/text' => [
                        'parent' => 'myPage',
                        'extends' => 'abstract/text',
                    ],
                ],
                [
                    'myPage/text' => [
                        'subs' => [
                            'abstract/text/sub' => [
                                'UiComponentName' => 'text',
                                'configuration' => []
                            ]
                        ],
                        'UiComponentName' => 'text',
                        'configuration' => [
                            'text' => 'test'
                        ]
                    ]
                ]
            ],
        ];
    }
}
