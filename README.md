# Security

[![Build](https://img.shields.io/travis/com/TYPO3-Initiatives/security/master.svg)](https://travis-ci.com/TYPO3-Initiatives/graphql)
[![Coverage](https://img.shields.io/codacy/coverage/a3ba86a8a97846e9a8bca68975f22c66/master.svg)](https://app.codacy.com/project/TYPO3-Initiatives/security/dashboard)
[![Code Quality](https://img.shields.io/codacy/grade/a3ba86a8a97846e9a8bca68975f22c66/master.svg)](https://app.codacy.com/project/TYPO3-Initiatives/security/dashboard)

This extension provides basic security features for TYPO3 CMS.

*This implementation is a proof-of-concept prototype and thus experimental development. Since not all planned features are implemented, this extension should not be used for production sites.*

## Installation

Use composer to install this extension in your project:

```bash
composer config repositories.security git https://github.com/typo3-initiatives/security
composer require typo3/cms-security
```

## Access control

Access rights are granted to users through the use of policies. The underlying model is known as [attribute-based access control](https://en.wikipedia.org/wiki/attribute-based_access_control) (ABAC). It makes use of boolean expressions which decide whether an access request is granted or not. Such a request typically contains the *resource*, *action*, *subject* and *environment attributes*. This extension implements a lightweight policy language and evaluation framework based on [Jiang, Hao & Bouabdallah, Ahmed (2017)](https://www.researchgate.net/publication/325873238).

The policy structure consists of *policy sets*, *policies* and *rules*. A *policy set* is a set of *policies* which in turn has a set of *rules*. Because not all policies are relevant to a given request every element includes the notion of a *target*. It determines whether a policy is applicable to a request by setting constraints on attributes using boolean expressions.

A policy is *applicable* if the access request satisﬁes the target. If so, its childrend are evaluated and the results returned by those children are combined using a combining algorithm. Otherwise, the policy is skipped without further examining its children and returns a *not applicable* *decision*.

The *rule* is the fundamental unit that can generate a conclusive *decision*. The *condition* of a *rule* is a more complex boolean expression that reﬁnes the applicability beyond the predicates speciﬁed by its *target*, and is optional. If a request satisﬁes both the *target* and *condition* of a *rule*, then the *rule* is applicable to the request and its *eﬀect* is returned as its *decision*. Otherwise, *not applicable* is returned.

Each *rule*, *policy* or *policy set* has an unique identifier and *obligations* which is used to specify the operations which should be performed after granting or denying an access request.

### Configuration

Policies are part of extension configurations and have to be defined with YAML (`Configuration/Yaml/Policies.yaml`). The root policy is in `TYPO3.CMS.Policy`. All policies are merged together in the topological sort of the extension depdency graph. Thus it is always possible to override existing policies.

As shown in the following example, an administrator is allowed to do anything, but all others are not allowed to do anything:

```yaml
---
TYPO3:
  CMS:
    Policy:
      description: 'Root policy set.'
      algorithm: highestPriority
      policies:
        Admin:
          target: 'hasAuthority("backend.role", "ADMIN")'
          description: 'Administrator policy'
          priority: 100
          rules:
            -
              effect: permit
        Default:
          description: 'Deny everything per default.'
          rules:
            -
              obligation:
                deny:
                  Feedback: ['Access denied.']
```

A **policy set** is a set of *policy sets* and *policies*. It has the following configuration fields:

| Field | Description |
| --- | --- |
| `description` | Optional description of the policy set. |
| `target` | Optional boolean expression indicating the *resource*, *action*, *subject* and *environment attributes* to which the *policy set* is applied. Default is `true`. |
| `alogrithm` | Optional name of a *combining algorithm* to compute the ﬁnal decision according to the results returned by its child policies, either `denyOverride`, `permitOverride`, `firstApplicable` or `highestPriority`. Default is `firstApplicable`. |
| `priority` | Optional number indicating the weight of the *policy set* when its decision conﬂicts with other policies under the `highestPriority` algorithm. Default is `1`. |
| `obligation` | Optional actions to take in case a particular conclusive decision (*permit* or *deny*) is reached. |
| `policies` | Required set of child policies (*policy sets* and *policies*). |

With configuration fields similar to a *policy set* a **policy** is a set of *rules*:

| Field | Description |
| --- | --- |
| `description` | Optional description of the policy. |
| `target` | Optional [boolean expression](https://symfony.com/doc/current/components/expression_language/syntax.html) indicating the *resource*, *action*, *subject* and *environment attributes* to which the *policy* is applied. Default is `true`. |
| `alogrithm` | Optional name of a *combining algorithm* to compute the ﬁnal decision according to the results returned by its child rules, either `denyOverride`, `permitOverride`, `firstApplicable` or `highestPriority`. Default is `firstApplicable`. |
| `priority` | Optional number indicating the weight of the *policy* when its decision conﬂicts with other policies under the `highestPriority` algorithm. Default is `1`. |
| `obligation` | Optional actions to take in case a particular conclusive decision (*permit* or *deny*) is reached. |
| `rules` | Required set of child *rules*. |

Unlike a *policy set* or a *policy*, a **rule** does not contain any leaf nodes:

| Field | Description |
| --- | --- |
| `target` | Optional [boolean expression](https://symfony.com/doc/current/components/expression_language/syntax.html) indicating the *resource*, *action*, *subject* and *environment attributes* to which the *policy* is applied. Default is `true`. |
| `effect` | Optional returned decision when the rule is applied, either `permit` or `deny`. Default is `deny`. |
| `condition` | Optional [boolean expression](https://symfony.com/doc/current/components/expression_language/syntax.html) that speciﬁes the condition for applying the rule. In comparison to a `target`, a `condition` is typically more complex. If either the `target` or the `condition` is not satisﬁed, a *not applicable* would be taken as the result instead of the speciﬁed `effect`. Default is `true`. |
| `priority` | Optional number indicating the weight of the *rule* when its decision conﬂicts with other rules under the `highestPriority` algorithm. Default is `1`. |
| `obligation` | Optional actions to take in case a particular conclusive decision (*permit* or *deny*) is reached. |

Policies may conflict and produce different *decisions* for the same request. To resolve this four kinds of
**combining algorithms** are provided. Each algorithm represents a different way for combining multiple local *decisions* into a single global *decision*:

| Algorithm | Description |
| --- | --- |
| `permitOverrides` | Returns *permit* if any *decision* evaluates to *permit* and returns *deny* if all *decisions* evaluate to *deny*. |
| `denyOverrides` | Returns *deny* if any *decision* evaluates to *deny* and returns *permit* if all *decisions* evaluate to *permit*. |
| `firstApplicable` | Returns the ﬁrst *decision* that evaluates to either of *permit* or *deny*. |
| `highestPriority` | Returns the highest priority *decision* that evaluates to either of *permit* or *deny*. If there are multiple equally highest priority *decisions* that conflict, then *deny overrides* algorithm would be applied among those highest priority *decisions*. |

Please note that for all of these *combining algorithms*, *not applicable* is returned if not any of the children is applicable.

### API

To perform an access request the *policy decision point* has to be used. It evaluates all policies and returns a *decision* either of *permit*, *deny* or *not applicable*:

```php
<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\EntityResourceAttribute;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\ReadActionAttribute;
use TYPO3\CMS\Security\Policy\PolicyDecision;
use TYPO3\CMS\Security\Policy\PolicyDecisionPoint;

$policyDecisionPoint = GeneralUtitlity::makeInstance(PolicyDecisionPoint::class);

$policyDecision = $policyDecisionPoint->authorize(
  [
    // resource to access
    'resource' => new EntityResourceAttribute('be_users'),
    // action on resource
    'action' => new ReadActionAttribute()
  ],
  // optional policy path to skip some top level processing
  'Vendor/ExamplePolicy'
);

if (!$policyDecision->isApplicable()) {
  // access request is not applicable
}

foreach ($policyDecision->getObligations() as $obligation) {
  // process obligations
}

if ($policyDecision->getValue() === PolicyDecision::PERMIT)
  // access is granted
}

// access is denied otherwise
```

To receive all operations which should be performed after denying or granting an access request the signal `enforcePolicyDecision` has to be used:

```php
<?php

\TYPO3\CMS\Core\Utility\GeneralUtilityGeneralUtility::makeInstance(
  \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
)->connect(
  \TYPO3\CMS\Security\Policy\PolicyEnforcement::class,
  'afterPolicyDecision',
  \Vendor\Example\Slot\PolicyDecisionSlot::class,
  'processDecision'
);
```

```php
<?php

namespace Vendor\Example\Slot;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Security\Policy\PolicyDecision;

class PolicyDecisionSlot
{
  public function processDecision(Context $context, PolicyDecision $decision, array $attributes)
  {
    // handle your operation
  }
}
```

## Development

Development for this extension is happening as part of the [persistence initiative](https://typo3.org/community/teams/typo3-development/initiatives/persistence/).
