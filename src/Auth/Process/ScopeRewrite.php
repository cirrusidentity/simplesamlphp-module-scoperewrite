<?php

namespace SimpleSAML\Module\scoperewrite\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Configuration;

class ScopeRewrite extends ProcessingFilter
{
    private string $newScope;

    /**
     * @var string[]
     */
    private array $attributesOldScopeToUsername;

    /**
     * @var string[]
     */
    private array $attributesReplaceScope;

    /**
     * @var string[]
     */
    private array $ignoreForScopes;

    private string $oldScopeSeparator;

    public function __construct(array &$config, mixed $reserved)
    {
        parent::__construct($config, $reserved);
        $conf = Configuration::loadFromArray($config);
        $this->newScope = $conf->getString('newScope');
        $this->attributesOldScopeToUsername = $conf->getOptionalArray('attributesOldScopeToUsername', [
            'eduPersonPrincipalName',
        ]);

        $this->attributesReplaceScope = $conf->getOptionalArray('attributesReplaceScope', [
            'eduPersonScopedAffiliation',
        ]);
        $this->ignoreForScopes = $conf->getOptionalArray('ignoreForScopes', []);
        $this->oldScopeSeparator = $conf->getOptionalString('oldScopeSeparator', '+');
    }

    public function process(array &$state): void
    {

        foreach ($this->attributesOldScopeToUsername as $attributeName) {
            if (!isset($state['Attributes'][$attributeName])) {
                continue;
            }

            $values = $state['Attributes'][$attributeName];
            $newValues = array();
            /** @var string $value */
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
            $state['Attributes'][$attributeName] = $newValues;
        }

        foreach ($this->attributesReplaceScope as $attributeName) {
            if (!isset($state['Attributes'][$attributeName])) {
                continue;
            }

            $values = $state['Attributes'][$attributeName];
            $newValues = array();
            /** @var string $value */
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
            $state['Attributes'][$attributeName] = $newValues;
        }
    }

    /**
     * Remove any scoping from the string
     *
     * @param  $value string to check
     * @return string unscoped version of string. If param has no scope then it is returned as is
     */
    private function unscope(string $value): string
    {
        $pos = strpos($value, '@');
        if ($pos === false) {
            return $value;
        } else {
            return substr($value, 0, $pos);
        }
    }
}
