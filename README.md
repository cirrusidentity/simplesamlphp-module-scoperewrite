# Overview

SSP module to simplify rewriting scoped attributes.

The module can move the old scope into the username portion, or replace it.

# Usage

## ScopeRewrite
In the below example if the user attributes were:

* eppn:  bob@test.com
* epsa:  student@test.com

Then after processing it would look like

* eppn: bob+test.com@example.com
* epsa: student@example.ecom

```php

'authproc' => array(
   500 => array(
            'class' => 'scoperewrite:ScopeRewrite',
            'attributesOldScopeToUsername' => array('urn:oid:1.3.6.1.4.1.5923.1.1.1.6'), // eduPersonPrincipalName
            'attributesReplaceScope' => array('urn:oid:1.3.6.1.4.1.5923.1.1.1.9'), // eduPersonScopedAffiliation
            'newScope' => 'example.com',
         );
```

## ScopeMap

Maps a set of scopes to new scopes.
```php

'authproc' => array(
   500 => array(
            'class' => 'scoperewrite:ScopeMap',
            'scopeMap' => [
                'student.example.edu' => 'example.edu',
                'staff.example.edu' => 'example.edu',
                'DOMAIN.EDU' => 'domain.edu'
            ],
            'srcAttribute' => 'eppn',
            'destAttribute' => 'rescopedEppn',
         );
```


# Installation

The module is not installed in packagist. You can add the repo.

```bash
composer config repositories.cirrus-scoperewrite git https://github.com/cirrusidentity/simplesamlphp-module-scoperewrite
composer require cirrusidentity/simplesamlphp-module-scoperewrite:dev-master
```


# Development

* Write tests. Run tests with `phpunit`. The `phpunit.xml` file already defines the location for the tests
* Check PSR-2 style. `phpcs  --standard=PSR2 lib`
