<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Assertions;


use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Trait Hierarchy
 * @package WagLabs\PawfectPHP\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
trait Hierarchy
{

    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $interface
     * @return bool
     */
    public function implements(ReflectionClass $reflectionClass, string $interface): bool
    {
        return $reflectionClass->implementsInterface($interface);
    }
}
