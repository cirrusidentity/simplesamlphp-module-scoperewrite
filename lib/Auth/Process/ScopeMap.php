<?php

namespace SimpleSAML\Module\scoperewrite\Auth\Process;

use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Configuration;
use SimpleSAML\Logger;

class ScopeMap extends ProcessingFilter
{

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
        $conf = Configuration::loadFromArray($config);
        $this->scopeMap = $conf->getArray('scopeMap');
        $this->srcAttribute = $conf->getString('srcAttribute');
        $this->destAttribute = $conf->getString('destAttribute');
        foreach ($this->scopeMap as $oldScope => $newScope) {
            if (!is_string($oldScope)) {
                throw new \Exception('scopeMap contains non-string key');
            }
            if (!is_string($newScope)) {
                throw new \Exception('scopeMap contains non-string value for key ' . $oldScope);
            }
        }
    }

    /**
     * Apply filter.
     *
     * @param array &$request the current request
     */
    public function process(&$request)
    {
        $newValues = [];
        $pattern = '/^(.*)@(.*)$/';
        foreach ($request['Attributes'][$this->srcAttribute] ?? [] as $value) {
            // pull off scope
            $matches = [];
            if (preg_match($pattern, $value, $matches) !== 1) {
                Logger::warning('Unable to get user + scope from value ' . $value . '. Passing it through to dstAttribute');
                $newValues[] = $value;
                continue;
            }
            $scope = $matches[2];
            $username = $matches[1];
            // check map. If in rescope, if not pass through
            if (array_key_exists($scope, $this->scopeMap)) {
                $newValues[] = $username . '@' . $this->scopeMap[$scope];
            } else {
                $newValues[] = $value;
            }
        }
        $request['Attributes'][$this->destAttribute] = $newValues;
    }
}