# Overview

SSP module to simplify rewriting scoped attributes.

The module can move the old scope into the username portion, or replace it.

# Usage

In the below example if the user attributes were:

* eppn:  bob@test.com
* epsa:  student@test.com

Then after processing it would look like

* eppn: bob+test.com@example.com
* epsa: sudent@example.ecom

```php

'authproc' => array(
   500 => array(
            'class' => 'scoperewrite:ScopeRewrite',
            'attributesOldScopeToUsername' => array('urn:oid:1.3.6.1.4.1.5923.1.1.1.6'), // eduPersonPrincipalName
            'attributesReplaceScope' => array('urn:oid:1.3.6.1.4.1.5923.1.1.1.9'), // eduPersonScopedAffiliation
            'proxyScope' => 'example.com'
         );
```

# Installation

The module is not installed in packagist. You can add the repo.

```bash
composer config repositories.cirrus-scoperewrite git https://github.com/cirrusidentity/simplesamlphp-module-scoperewrite
composer require cirrusidentity/simplesamlphp-module-scoperewrite:dev-master
```


# Development

Write tests. Run tests with `phpunit tests/`

# TODO list

* SSP dependencies needed by the tests were just copied into the test folder. Composer should be used to pull down the dependencies for testing.