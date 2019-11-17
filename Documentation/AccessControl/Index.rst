.. include:: ../Includes.txt

.. index:: Access Control

.. _access-control:

Access Control
==============

Access rights are granted to users through the use of policies. The
underlying model is known as `attribute-based access control (ABAC) <https://en.wikipedia.org/wiki/attribute-based_access_control>`__.
It makes use of boolean expressions which decide whether an access
request is granted or not. Such a request typically contains the
*resource*, *action*, *subject* and *environment attributes*.

The security layer implements a lightweight policy language and
evaluation framework based on `Jiang, Hao & Bouabdallah, Ahmed (2017) <https://www.researchgate.net/publication/325873238>`__.

The policy structure consists of *policy sets*, *policies* and
*rules*. A *policy set* is a set of *policies* which in turn has a
set of *rules*. Because not all policies are relevant to a given
request every element includes the notion of a *target*. It
determines whether a policy is applicable to a request by setting
constraints on attributes using boolean expressions.

A policy is *applicable* if the access request satisfies the target.
If so, its childrend are evaluated and the results returned by those
children are combined using a combining algorithm. Otherwise, the
policy is skipped without further examining its children and returns
a *not applicable* *decision*.

The *rule* is the fundamental unit that can generate a conclusive
*decision*. The *condition* of a *rule* is a more complex boolean
expression that refines the applicability beyond the predicates
specified by its *target*, and is optional. If a request satisfies
both the *target* and *condition* of a *rule*, then the *rule* is
applicable to the request and its *eﬀect* is returned as its *decision*.
Otherwise, *not applicable* is returned.

Each *rule*, *policy* or *policy set* has an unique identifier and
*obligations* which is used to specify the operations which should
be performed after granting or denying an access request.

.. _access-control-expressions:

Expressions
-----------

The `expression language <https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Feature-85828-MoveSymfonyExpressionLanguageHandlingIntoEXTcore.html>`__,
integral part of TYPO3 CMS since version 9, is the basis for the
*targets* and *conditions* of all policy rules. Based on this, the
following attributes and functions are provided:

===============  =========================================================================================================================================================================================================================
Attribute        Description
===============  =========================================================================================================================================================================================================================
:php:`resource`  Is an entity to be protected from unauthorized use. The *resource* is directly provided by the access request.
:php:`subject`   Represents the entity requesting to perform an operation upon the *resource*. It is provided indirectly through the given context of the policy decision point and can not modifed or set by the access request directly.
:php:`action`    The operations to be performed on the *resource*. Like the *resource* it is also provided by the access request.
===============  =========================================================================================================================================================================================================================

==========================================================================  ===============================================================================================================================================================
Function                                                                    Description
==========================================================================  ===============================================================================================================================================================
:php:`hasAuthority(string $type, string $identifier): bool`                 Returns whether the current *subject* has a principal indicated by `type` and `identifier`.
:php:`hasPermission(ResourceAttribute $resource, ActionAttribute $action)`  Returns whether the current *subject* has the permission to perform an operation the given `resource` indicated by `action`.
:php:`constant(string $name): mixed`                                        Returns the value of the constant indicated by `name`.
==========================================================================  ===============================================================================================================================================================

.. _access-control-configuration:

Configuration
-------------

Policies are part of extension configurations and have to be defined
with YAML :file:`Configuration/Security/AccessControl/Policies.yaml`.
The root policy is in :yaml:`TYPO3.CMS.Policy`. All policies are
merged together in the topological sort of the extension depdency
graph. Thus it is always possible to override existing policies.

As shown in the following example, an administrator is allowed to
do anything, but all others are not allowed to do anything:

.. code-block:: yaml

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

A **policy set** is a set of *policy sets* and *policies*. It has the following configuration fields:

===================  ==============================================================================================================================================================================================================================================
Field                Description
===================  ==============================================================================================================================================================================================================================================
:yaml:`description`  Optional description of the policy set. 
:yaml:`target`       Optional boolean expression indicating the *resource*, *action*, *subject* and *environment attributes* to which the *policy set* is applied. Default is `true`.
:yaml:`alogrithm`    Optional name of a *combining algorithm* to compute the final decision according to the results returned by its child policies, either `denyOverride`, `permitOverride`, `firstApplicable` or `highestPriority`. Default is `firstApplicable`.
:yaml:`priority`     Optional number indicating the weight of the *policy set* when its decision conﬂicts with other policies under the `highestPriority` algorithm. Default is `1`.
:yaml:`obligation`   Optional actions to take in case a particular conclusive decision (*permit* or *deny*) is reached.
:yaml:`policies`     Required set of child policies (*policy sets* and *policies*).
===================  ==============================================================================================================================================================================================================================================

With configuration fields similar to a *policy set* a **policy** is a set of *rules*:

===================  ===========================================================================================================================================================================================================================================
Field                Description
===================  ===========================================================================================================================================================================================================================================
:yaml:`description`  Optional description of the policy.
:yaml:`target`       Optional `boolean expression <https://symfony.com/doc/current/components/expression_language/syntax.html>` indicating the *resource*, *action*, *subject* and *environment attributes* to which the *policy* is applied. Default is `true`.
:yaml:`alogrithm`    Optional name of a *combining algorithm* to compute the final decision according to the results returned by its child rules, either `denyOverride`, `permitOverride`, `firstApplicable` or `highestPriority`. Default is `firstApplicable`.
:yaml:`priority`     Optional number indicating the weight of the *policy* when its decision conﬂicts with other policies under the `highestPriority` algorithm. Default is `1`.
:yaml:`obligation`   Optional actions to take in case a particular conclusive decision (*permit* or *deny*) is reached.
:yaml:`rules`        Required set of child *rules*.
===================  ===========================================================================================================================================================================================================================================

Unlike a *policy set* or a *policy*, a **rule** does not contain any leaf nodes:

==================  ======================================================================================================================================================================================================================================================================================================================================================================================================
Field               Description
==================  ======================================================================================================================================================================================================================================================================================================================================================================================================
:yaml:`target`      Optional `boolean expression <https://symfony.com/doc/current/components/expression_language/syntax.html>` indicating the *resource*, *action*, *subject* and *environment attributes* to which the *policy* is applied. Default is `true`.
:yaml:`effect`      Optional returned decision when the rule is applied, either `permit` or `deny`. Default is `deny`.
:yaml:`condition`   Optional `boolean expression <https://symfony.com/doc/current/components/expression_language/syntax.html>` that specifies the condition for applying the rule. In comparison to a `target`, a `condition` is typically more complex. If either the `target` or the `condition` is not satisfied, a *not applicable* would be taken as the result instead of the specified `effect`. Default is `true`.
:yaml:`priority`    Optional number indicating the weight of the *rule* when its decision conﬂicts with other rules under the `highestPriority` algorithm. Default is `1`.
:yaml:`obligation`  Optional actions to take in case a particular conclusive decision (*permit* or *deny*) is reached.
==================  ======================================================================================================================================================================================================================================================================================================================================================================================================

Policies may conflict and produce different *decisions* for the same request. To resolve this four kinds of
**combining algorithms** are provided. Each algorithm represents a different way for combining multiple local *decisions* into a single global *decision*:

=======================  ============================================================================================================================================================================================================================================================
Algorithm                Description
=======================  ============================================================================================================================================================================================================================================================
:yaml:`permitOverrides`  Returns *permit* if any *decision* evaluates to *permit* and returns *deny* if all *decisions* evaluate to *deny*.
:yaml:`denyOverrides`    Returns *deny* if any *decision* evaluates to *deny* and returns *permit* if all *decisions* evaluate to *permit*.
:yaml:`firstApplicable`  Returns the first *decision* that evaluates to either of *permit* or *deny*.
:yaml:`highestPriority`  Returns the highest priority *decision* that evaluates to either of *permit* or *deny*. If there are multiple equally highest priority *decisions* that conflict, then *deny overrides* algorithm would be applied among those highest priority *decisions*.
=======================  ============================================================================================================================================================================================================================================================

Please note that for all of these *combining algorithms*, *not applicable* is returned if not any of the children is applicable.

API
---

To perform an access request the *policy decision point* has to be used. It evaluates all policies and returns a *decision* either of *permit*, *deny* or *not applicable*:

.. code-block:: php

   <?php

   use TYPO3\CMS\Core\Utility\GeneralUtility;
   use TYPO3\CMS\Security\AccessControl\Attribute\ResourceAttribute;
   use TYPO3\CMS\Security\AccessControl\Attribute\ReadActionAttribute;
   use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;
   use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecisionPoint;

   $policyDecisionPoint = GeneralUtitlity::makeInstance(PolicyDecisionPoint::class);

   $policyDecision = $policyDecisionPoint->authorize(
   [
       // concrete resource to access
       'resource' => new ResourceAttribute('identifier'),
       // concrete action on resource
       'action' => new ActionAttribute()
   ]
   );

   if (!$policyDecision->isApplicable()) {
       // access request is not applicable
   }

   // process determining policy rule
   $determinigRule = $policyDecision->getRule();

   foreach ($policyDecision->getObligations() as $obligation) {
       // process obligations
   }

   if ($policyDecision->getValue() === PolicyDecision::PERMIT)
       // access is granted
   }

   // access is denied otherwise

To receive all operations which should be performed after denying
or granting an access request the event :php:`\TYPO3\CMS\Security\AccessControl\Event\PolicyDecisionEvent` 
has to be used:

.. code-block:: yaml

   services:
   Vendor\Example\EventListener\PolicyDecisionListener:
     tags:
       -
         name: event.listener
         identifier: 'vendor-policy-decision-listener'
         event: TYPO3\CMS\Security\AccessControl\Event\PolicyDecisionEvent

.. code-block:: php

   <?php

   namespace Vendor\Example\EventListener;

   use TYPO3\CMS\Security\AccessControl\Event\PolicyDecisionEvent;

   class PolicyDecisionListener
   {
       public function __invoke(PolicyDecisionEvent $event)
       {
           // ...
       }
   }

To provide additional data for an attribute before an access
request the event :php:`\TYPO3\CMS\Security\AccessControl\Event\AttributeRetrivalEvent`
can be used:

```yaml
   services:
     Vendor\Example\EventListener\AttributeRetrivalListener:
      tags:
        -
          name: event.listener
          identifier: 'vendor-attribute-retrival-listener'
          event: TYPO3\CMS\Security\AccessControl\Event\AttributeRetrivalEvent
```

```php
   <?php

   namespace Vendor\Example\EventListener;

   use TYPO3\CMS\Security\AccessControl\Event\AttributeRetrivalEvent;

   class PolicyDecisionPoint
   {
      public function __invoke(AttributeRetrivalEvent $event)
      {
          // ...
      }
   }

Design Principals
-----------------

Whenever possible the authorization logic should be part of
a policy. Thus its auditable and changeable. For reasons of
the performance or complexity it might be not possible. Then
it's recommended to extend the expression language with a
custom function.