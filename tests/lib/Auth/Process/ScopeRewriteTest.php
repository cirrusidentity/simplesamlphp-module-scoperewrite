<?php

namespace Auth\Process;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\scoperewrite\Auth\Process\ScopeRewrite;

class ScopeRewriteTest extends TestCase
{
    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param  array $config The filter configuration.
     * @param  array $request The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request): array
    {
        $filter = new ScopeRewrite($config, null);
        $filter->process($request);
        return $request;
    }

    /**
     * Test with no attributes
     */
    public function testNoAttributes(): void
    {
        $config = array('newScope' => 'tester.com');
        $request = array(
            'Attributes' => array(),
        );
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEmpty($attributes);
    }

    /**
     * Test the most basic functionality.
     */
    public function testScopeRewriteDefaultConfig(): void
    {
        $config = array('newScope' => 'tester.com');
        $request = array(
            'Attributes' => array(
                'eduPersonPrincipalName' => array('joe@home.com'),
                'eduPersonScopedAffiliation' => array('student@home.com', 'staff@home.com')),
        );
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals(
            array('joe+home.com@tester.com'),
            $attributes['eduPersonPrincipalName'],
            'Eppn should have old scope as part of value.'
        );
        $this->assertEquals(
            array('student@tester.com', 'staff@tester.com'),
            $attributes['eduPersonScopedAffiliation'],
            'Scoped affilation should have scope changed'
        );
    }

    /**
     * Test optional enablement
     */
    public function testIgnoreScope(): void
    {
        $config = array(
            'newScope' => 'tester.com',
            'ignoreForScopes' => [
                'home.com'
            ]
        );
        $request = array(
            'Attributes' => array(
                'eduPersonPrincipalName' => array('joe@home.com'),
                'eduPersonScopedAffiliation' => array('student@home.com', 'staff@not-ignored.com')),
        );
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals(
            array('joe@home.com'),
            $attributes['eduPersonPrincipalName'],
            'Eppn has scope that should not be changed'
        );
        $this->assertEquals(
            array('student@home.com', 'staff@tester.com'),
            $attributes['eduPersonScopedAffiliation'],
            'Scoped affiliation should have 1 scope changed'
        );
    }

    /**
     * Test all config options
     */
    public function testScopeRewriteCustomConfig(): void
    {
        $config = array(
            'newScope' => 'tester.com',
            'attributesOldScopeToUsername' => array('username1', 'username2'),
            'attributesReplaceScope' => array('rewrite1', 'rewrite2'),
        );
        $request = array(
            'Attributes' => array(
                'username1' => array('joe@home.com'),
                'username2' => array('jeff'), // not pre-scoped test.
                'rewrite1' => array('student@home.com'),
                'rewrite2' => array("staff"), // not pre-scoped test
            ),
        );
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals(
            array('joe+home.com@tester.com'),
            $attributes['username1'],
            'username1 should have old scope as part of value.'
        );
        $this->assertEquals(array('jeff@tester.com'), $attributes['username2']);
        $this->assertEquals(array('student@tester.com'), $attributes['rewrite1']);
        $this->assertEquals(array('staff@tester.com'), $attributes['rewrite2']);
    }



    /**
     * Test picking a separator for the old scope and username
     */
    public function testOldScopeSeparator(): void
    {
        $config = array(
            'newScope' => 'tester.com',
            'attributesOldScopeToUsername' => array('username1', 'username2'),
            'oldScopeSeparator' => '(at)',
        );
        $request = array(
            'Attributes' => array(
                'username1' => array('joe@home.com', 'joe+something@example.com'),
                'username2' => array('jeff'), // not pre-scoped test.

            ),
        );
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals(
            array('joe(at)home.com@tester.com', 'joe+something(at)example.com@tester.com'),
            $attributes['username1'],
            'username1 should have old scope as part of value.'
        );
        $this->assertEquals(array('jeff@tester.com'), $attributes['username2']);
    }
}
