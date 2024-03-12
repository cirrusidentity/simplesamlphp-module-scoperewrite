<?php

namespace SimpleSAML\Module\scoperewrite\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;

class ScopeRewrite extends ProcessingFilter
{
    private $newScope;

    private $attributesOldScopeToUsername = array(
        'eduPersonPrincipalName',
    );

    private $attributesReplaceScope = array(
        'eduPersonScopedAffiliation',
    );

    private $ignoreForScopes = array();

    private $oldScopeSeparator = '+';

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        if (empty($config['newScope'])) {
            throw new \SimpleSAML\Error\Exception('ScopeRewrite: "newScope" value must be provided');
        }

        $this->newScope = $config['newScope'];

        if (array_key_exists('attributesOldScopeToUsername', $config)) {
            $this->attributesOldScopeToUsername = $config['attributesOldScopeToUsername'];
        }
        if (array_key_exists('attributesReplaceScope', $config)) {
            $this->attributesReplaceScope = $config['attributesReplaceScope'];
        }
        if (array_key_exists('ignoreForScopes', $config)) {
            $this->ignoreForScopes = $config['ignoreForScopes'];
        }
        if (array_key_exists('oldScopeSeparator', $config)) {
            $this->oldScopeSeparator = $config['oldScopeSeparator'];
        }
    }

    /**
     * Apply filter.
     *
     * @param array &$request the current request
     */
    public function process(array &$request): void
    {

        foreach ($this->attributesOldScopeToUsername as $attributeName) {
            if (!isset($request['Attributes'][$attributeName])) {
                continue;
            }

            $values = $request['Attributes'][$attributeName];
            $newValues = array();
            foreach ($values as $value) {
                $scope = '';
                if (($pos = strpos($value, '@')) !== false) {
                    $scope = substr($value, $pos + 1);
                }
                // Check if rewrite this scope or not
                if (in_array($scope, $this->ignoreForScopes)) {
                    $newValues[] = $value;
                } else {
                    $newValues[] = str_replace('@', $this->oldScopeSeparator, $value) . '@' . $this->newScope;
                }
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
                $scope = '';
                if (($pos = strpos($value, '@')) !== false) {
                    $scope = substr($value, $pos + 1);
                }
                // Check if rewrite this scope or not
                if (in_array($scope, $this->ignoreForScopes)) {
                    $newValues[] = $value;
                } else {
                    $newValues[] = $this->unscope($value) . '@' . $this->newScope;
                }
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
        } else {
            return (substr($string, 0, $pos));
        }
    }
}
