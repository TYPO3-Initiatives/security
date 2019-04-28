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
