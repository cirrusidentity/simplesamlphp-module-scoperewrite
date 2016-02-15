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
class sspmod_scoperewrite_Auth_Process_FilterAttributes extends SimpleSAML_Auth_ProcessingFilter
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
            throw new SimpleSAML_Error_Exception('"newScope" value must be provided');
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
                //FIXME: add put old scope into username
                $newValues[] = $value . '@' . $this->newScope;
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
                //FIXME: remove old scope.
                $newValues[] = $value . '@' . $this->newScope;
            }
            $request['Attributes'][$attributeName] = $newValues;
        }

    }
}

