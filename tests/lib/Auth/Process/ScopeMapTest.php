<?php

namespace Auth\Process;

use SimpleSAML\Module\scoperewrite\Auth\Process\ScopeMap;

class ScopeMapTest extends \PHPUnit\Framework\TestCase
{

    private array $testConfig = [
        'class' => 'scoperewrite:ScopeMap',
        'scopeMap' => [
            'student.example.edu' => 'example.edu',
            'staff.example.edu' => 'example.edu',
            'DOMAIN.EDU' => 'domain.edu'
        ],
        'srcAttribute' => 'scopedAttr',
        'destAttribute' => 'rescopedAttr',
    ];

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param  array $config The filter configuration.
     * @param  array $request The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request): array
    {
        $filter = new ScopeMap($config, null);
        $filter->process($request);
        return $request;
    }

    /**
     * Test with no attributes
     */
    public function testNoAttributes(): void
    {
        $request = array(
            'Attributes' => array(),
        );
        $result = self::processFilter($this->testConfig, $request);
        $attributes = $result['Attributes'];
        $this->assertEmpty($attributes, var_export($attributes, true));
    }

    public function testMapping(): void
    {
        $request = array(
            'Attributes' => [
                'gn' => ['name'],
                'scopedAttr' => [
                    'user@nochange.com',
                    'student@student.example.edu',
                    'staff@staff.example.edu',
                    'user@DOMAIN.EDU',
                    'noscope',
                    'mult@ple@s'
                ]
            ],
        );
        $expectedAttributes = $request['Attributes'] + [
                'rescopedAttr' => [
                    'user@nochange.com',
                    'student@example.edu',
                    'staff@example.edu',
                    'user@domain.edu',
                    'noscope',
                    'mult@ple@s'
                ]
            ];
        $result = self::processFilter($this->testConfig, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals($expectedAttributes, $attributes);
    }
}
