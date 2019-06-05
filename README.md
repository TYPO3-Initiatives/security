# Security

[![Build Status](https://travis-ci.com/TYPO3-Initiatives/security.svg?branch=master)](https://travis-ci.com/TYPO3-Initiatives/security)

This extension provides basic security features for TYPO3 CMS.

*This implementation is a proof-of-concept prototype and thus experimental development. Since not all planned features are implemented, this extension should not be used for production sites.*

## Installation

Use composer to install this extension in your project:

```bash
composer config repositories.security git https://github.com/typo3-initiatives/security
composer require typo3/cms-security
```

## Development

You can use the following `composer.json` if you want to contribute:

```json
{
    "name": "typo3/security",
    "type": "project",
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/typo3-initiatives/security"
        }
    ],
    "require": {
        "typo3/cms-security": "10.0.*@dev"
    },
    "require-dev": {
        "typo3/testing-framework": "^5.0"
    },
    "prefer-stable": true,
    "minimum-stability": "dev"
}
```

## Permission API

The permission API supports [access-control lists](https://en.wikipedia.org/wiki/Access-control_list) (ACL). Thus you have always an *object* and a *subject*. Each object has an access-control list which you can use use to check if a subject has the right to access the object.

To do so you have to retrive all subject identities and the access-control list of a specific object. How to do that for a backend user and a table is shown by the following example:

```php
use TYPO3\CMS\Backend\Permission\TablePermissionRetrivalStrategy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\PermissionProvider;
use TYPO3\CMS\Security\Permission\SubjectIdentityProvider;

$subjectIdentities = GeneralUtility::makeInstance(SubjectIdentityProvider::class)
    ->getSubjectIdentities($GLOBALS['BE_USER']);

$permissionList = GeneralUtility::makeInstance(PermissionProvider::class)
    ->findList(new ObjectIdentity('table/pages')), $subjectIdentities);

if ($permissionList->isGranted([TablePermissionRetrivalStrategy::PERMISSION_READ], $subjectIdentities))) {
    // access is granted [...]
} else {
   // access is denied [...]
}
```

Entries of the access-control list are used to check if access is granted or not. Each entry has the following fields:

 * *subject*
 * *mask*
 * *priority*
 * *grantig flag* 
 
The subject and the mask are part of every permission check and thus they will be used to find the matching entry. From all matching entries the one with the highest priority will be applied. The granting flag controls if the permission will be granted or not.

You can easily create your own access-control list. How to do that for a table is shown by the following example:

```php
use TYPO3\CMS\Backend\Permission\BackendAdministratorIdentity;
use TYPO3\CMS\Backend\Permission\TablePermissionRetrivalStrategy;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\PermissionGrantingStrategy;
use TYPO3\CMS\Security\Permission\PermissionEntry;
use TYPO3\CMS\Security\Permission\PermissionList;

$permissionList = new PermissionList(
    new ObjectIdentity('table/pages'),
    new PermissionGrantingStrategy()
);

$permissionEntry = new PermissionEntry(
    TablePermissionRetrivalStrategy::PERMISSION_READ, 
    new BackendAdministratorIdentity(), 
    30
);

$permissionList->add($permissionEntry);
```

Inheritance of entries is also supported by setting a parent access-control list:

```php
$permissionList->setParent($parentPermissionList);
$permissionList->setInheriting(true);
```

To provide your own permission list you have to implement a permission retrival strategy:

```php
namespace Vendor;

use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\PermissionListInterface;
use TYPO3\CMS\Security\Permission\PermissionRetrivalStrategyInterface;

class CustomPermissionRetrivalStrategy implements PermissionRetrivalStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canRetrive(ObjectIdentityInterface $objectIdentity): bool
    {
        // put your own logic here
    }

    /**
     * {@inheritdoc}
     */
    public function retrive(ObjectIdentityInterface $objectIdentity, array $subjectIdentities = []): PermissionListInterface
    {
        // put your own logic here
    }
}
```

Your custom retrival strategy will be available when it's registered in your `ext_localconf.php`:

```php
$GLOBALS['TYPO3_CONF_VARS']['SYS']['security']['permissionRetrival'][] 
    = \Vendor\CustomPermissionRetrivalStrategy::class;
```
