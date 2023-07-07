pawfect-php
-----------
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![codecov](https://codecov.io/gh/waglabs/pawfect-php/branch/main/graph/badge.svg)](https://codecov.io/gh/waglabs/pawfect-php)
![CI](https://github.com/waglabs/pawfect-php/actions/workflows/ci.yml/badge.svg?branch=main)

PawfectPHP is a simple and extensible framework (_currently in development_) for writing rules to ensure PHP code meets
engineer-defined code and architecture standards.

# Install

1. Add `git@github.com:waglabs/pawfect-php.git` to your
   composer [repositories](https://getcomposer.org/doc/05-repositories.md#loading-a-package-from-a-vcs-repository)
1. Run `composer require --dev waglabs/pawfect-php`

# Usage

`php ./vendor/bin/pawfect-php scan ./rules ./src`

# Using PawfectPHP

As a codebase gets more complex, it's pertinent to establish standards enforcement for things that traditional static
analysis tools can't tackle as the rules themselves are specific to the codebase. PawfectPHP intends to fill that niche
by providing a simple framework to enforce code standards that deal with class relationships and custom code
architecture. Additionally, PawfectPHP is designed for gradual adoption, which allows it to be implemented as your
codebase evolves.

PawfectPHP is structured around the concept of "rules". Rules are a way to codify standards such as:

- Classes in the `Foo\Bar` namespace only depend on classes that are in the `Fiz\Buz` namespace
- Classes with a method called `invoke` must have parameters that don't extend from `Foo\Bar\Baz`

## Writing Rules

Rules must implement [`WagLabs\PawfectPHP\RuleInterface`](./src/RuleInterface.php), and can live anywhere in the project
PawfectPHP is being integrated into, however `./rules` is recommended. Note that PawfectPHP requires a PSR-4 compliant
codebase using `composer` for autoloading, including for your PawfectPHP rule classes.

Rules look like the following:

```php
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Assertions\Methods;
use WagLabs\PawfectPHP\ReflectionClass;

class SimpleRule extends AbstractRule
{

    use Methods;

    public function supports(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->isInstantiable();
    }

    public function execute(ReflectionClass $reflectionClass)
    {
        $this->assert($this->hasPublicMethod($reflectionClass, '__construct'));
    }

    public function getName(): string
    {
        return 'simple-rule';
    }

    public function getDescription(): string
    {
        return 'Ensure that instantiable classes have a `__construct` method';
    }
}
```

There are two methods of note above, `supports` and `execute`.

`supports` is used to determine if the discovered class should be checked against the rule, and `execute` is called to
actually run the rule against the class.

Once this rule is defined, we can run (assuming our application's code is
in `./src`): `php ./vendor/bin/pawfect-php scan ./rules ./src`

## ReflectionClass

The ReflectionClass used internally is essentially a pass through to an instance
of [\Roave\BetterReflection\Reflection\ReflectionClass](https://github.com/Roave/BetterReflection), with some added
data, namely:

- an instance of `\SplFileInfo` pointing at the file PawfectPHP discovered the class in (`getSplFileInfo()`)
- an array of FQNs for the classes the discovered class `use`s (`getUses()`)

## AbstractRule

The [`\WagLabs\PawfectPHP\AbstractRule`](./src/AbstractRule.php) contains one method, `assert`, which checks if the
passed boolen parameter is true. If it is false, an instance
of [`\WagLabs\PawfectPHP\FailedAssertionException`](src/FailedAssertionException.php) is thrown, which is interpreted as
a failure of the rule. Note that a `false` result from the `execute` method will also be treated as a failure, and an
empty response or a `true` response will be treated as the rule passing.

## WagLabs\PawfectPHP\Assertions

[`WagLabs\PawfectPHP\Assertions`](./src/Assertions) contains helper traits for rules to abstract out complexity from
inspecting the `ReflectionClass`. It is recommended that you write your own helper traits if needed.

## WagLabs\PawfectPHP\Annotations

[`WagLabs\PawfectPHP\Annotations\ApplyRule`](./src/Annotations/ApplyRule.php) is a simple way to include rules upon your
classes as your codebase evolves and/or determinations of rule support is costly.

```php
use WagLabs\PawfectPHP\Annotations\ApplyRule;
/**
 * @ApplyRule("single-rule")
 */
```

```php
use WagLabs\PawfectPHP\AbstractAnnotationRule;
use WagLabs\PawfectPHP\ReflectionClass;

class SingleRule extends AbstractAnnotationRule
{

  public function execute(ReflectionClass $reflectionClass)
  {
  
  }
  
  public function getName() : string
  {
      return 'single-rule';
  }
  
  public function getDescription() : string
  {
      return 'Current accepted best practice and coding standard that applies to our codebase.';
  }

}
```

or fine-tune your `->supports()` to only inspect annotated classes

```php
...
use WagLabs\PawfectPHP\Assertions\Annotation;

class SingleRule extends AbstractRule
{

  use Annotation;

  public function supports(ReflectionClass $reflectionClass): bool
  {
      if (!$this->matchesApplyRuleAnnotation($reflectionClass, $this->getName())) {
          return false;
      }
    
    // determine rule support by inspecting the ReflectionClass instance
  }
...
```

### @ApplyRule Parameters

The default value of the annotation is applied to the names parameter as an array.
e.g.: `@ApplyRule("single-rule")` `@ApplyRule({"list-rule", "another-rule"})`

#### names: array\<string\>

This is a list of strings to match the rule names exactly.

#### regex: string

Pass a valid regex expression to be applied. Will not be applied if names is not empty.
e.g.: `@ApplyRule(regex="/^starts-with-/")` `@ApplyRule(regex="/(contains|keywords)/")`

# Advanced usage

## Using the ReflectionClassLoader in rules

Internally `league/container` (with the `ReflectionContainer` delegate) is used to build instances of rule classes, and
the following classes are registered in the container:

- The shared instance `\WagLabs\PawfectPHP\ReflectionClassLoaderInterface`, which can be used to load `ReflectionClass`
  instances given an FQN
- The shared instance of `\WagLabs\PawfectPHP\RuleRepositoryInterface`

## Class AST access

As PawfectPHP uses `roave/better-reflection` under the hood, which in turn uses `nikic/php-parser`, you can access the
`\PhpParser\Node\Stmt\ClassLike` instance for the class by calling `getAst()` on the `BetterReflection` object. 
