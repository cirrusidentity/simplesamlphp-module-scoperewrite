<?php

//figure out a better way to include this
require_once('SimpleAuthProcessingDependency.php');
require_once(dirname(dirname(dirname(dirname(__DIR__)))) . '/lib/Auth/Process/FilterAttributes.php');
class Test_sspmod_scoperewrite_Auth_Process_FilterAttributes extends PHPUnit_Framework_TestCase
{

    /**
     * Helper function to run the filter with a given configuration.
     *
     * @param array $config  The filter configuration.
     * @param array $request  The request state.
     * @return array  The state array after processing.
     */
    private static function processFilter(array $config, array $request)
    {
        $filter = new sspmod_scoperewrite_Auth_Process_FilterAttributes($config, NULL);
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
                'eduPersonScopedAffiliation' => array('student@home.com','staff@home.com')),
        );
        $result = self::processFilter($config, $request);
        $attributes = $result['Attributes'];
        $this->assertEquals(array('joe+home.com@tester.com'), $attributes['eduPersonPrincipalName'], 'Eppn should have old scope as part of value.');
        $this->assertEquals(array('student@tester.com','staff@tester.com'), $attributes['eduPersonScopedAffiliation'], 'Scoped affilation should have scope changed');
    }
}
