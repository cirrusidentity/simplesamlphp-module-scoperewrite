<?php

class Test_sspmod_scoperewrite_Auth_Process_ScopeRewrite extends PHPUnit_Framework_TestCase
{

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param  array $config The filter configuration.
     * @param  array $request The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request)
    {
        $filter = new sspmod_scoperewrite_Auth_Process_ScopeRewrite($config, null);
        $filter->process($request);
        return $request;
    }

    /**
     * Test with not attributes
     */
    public function testNoAttributes()
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
    public function testScopeRewriteDefaultConfig()
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
     * Test all config options
     */
    public function testScopeRewriteCustomeConfig()
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
}
