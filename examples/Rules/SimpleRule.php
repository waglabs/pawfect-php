<?php

namespace WagLabs\PawfectPHP\Examples\Rules;

use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Assertions\Methods;
use WagLabs\PawfectPHP\FailedAssertionException;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class SimpleRule
 * @package WagLabs\PawfectPHP\Examples\Rules
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class SimpleRule extends AbstractRule
{

    use Methods;

    public function supports(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->isInstantiable();
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool|void
     * @throws FailedAssertionException
     */
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
