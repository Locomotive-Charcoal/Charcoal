<?php

namespace Charcoal\Tests\Config\Mock;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;
use Charcoal\Tests\Config\Mock\MacroTrait;

/**
 * Mock object of {@see \Charcoal\Config\AbstractConfig}
 */
class MacroConfig extends AbstractConfig
{
    use MacroTrait;

    /**
     * @return array
     */
    public function defaults(): array
    {
        return [
            'foo' => -3,
            'baz' => 'garply',
            'erd' => true,
        ];
    }
}
