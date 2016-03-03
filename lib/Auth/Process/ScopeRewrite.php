<?php

/**
 * Filter to remove
 * * all attributes if there is no `shibmd:Scope` value for the IdP
 * * attribute values which are not properly scoped
 * * configured scopeAttribute if it doesn't match against a value from `shibmd:Scope`.
 *
 * Note:
 * * regexp in scope values are not supported.
 * * Configured attribute names MUST match with names in attributemaps. It is case-sensitive.
 *
 * @author Adam Lantos  NIIF / Hungarnet
 * @author Gyula Szabo  NIIF / Hungarnet
 * @author Tamas Frank  NIIF / Hungarnet
 */
class sspmod_scoperewrite_Auth_Process_ScopeRewrite extends SimpleSAML_Auth_ProcessingFilter
{
    private $newScope;

    private $attributesOldScopeToUsername = array(
        'eduPersonPrincipalName',
    );

    private $attributesReplaceScope = array(
        'eduPersonScopedAffiliation',
        );

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        if (empty($config['newScope'])) {
            throw new SimpleSAML_Error_Exception('ScopeRewrite: "newScope" value must be provided');
        }

        $this->newScope = $config['newScope'];

        if (array_key_exists('attributesOldScopeToUsername', $config)) {
            $this->attributesOldScopeToUsername = $config['attributesOldScopeToUsername'];
        }
        if (array_key_exists('attributesReplaceScope', $config)) {
            $this->attributesReplaceScope = $config['attributesReplaceScope'];
        }
    }

    /**
     * Apply filter.
     *
     * @param array &$request the current request
     */
    public function process(&$request)
    {

        foreach ($this->attributesOldScopeToUsername as $attributeName) {
            if (!isset($request['Attributes'][$attributeName])) {
                continue;
            }

            $values = $request['Attributes'][$attributeName];
            $newValues = array();
            foreach ($values as $value) {
                $newValues[] = str_replace('@', '+', $value) . '@' . $this->newScope;
            }
            $request['Attributes'][$attributeName] = $newValues;
        }

        foreach ($this->attributesReplaceScope as $attributeName) {
            if (!isset($request['Attributes'][$attributeName])) {
                continue;
            }

            $values = $request['Attributes'][$attributeName];
            $newValues = array();
            foreach ($values as $value) {
                $newValues[] = $this->unscope($value) . '@' . $this->newScope;
            }
            $request['Attributes'][$attributeName] = $newValues;
        }

    }

    /**
     * Remove any scoping from the string
     *
     * @param  $string string to check
     * @return string unscope version of string. If param has no scope then it is returned as is
     */
    private function unscope($string) 
    {
        $pos = strpos($string, '@');
        if ($pos === false) {
            return $string; 
        }
        else {
            return(substr($string, 0, $pos)); 
        }
    }
}

