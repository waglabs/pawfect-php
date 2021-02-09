<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\Assertions;

use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Trait Methods
 * @package WagLabs\PawfectPHP\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
trait Methods
{

    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $methodName
     * @return bool
     */
    public function hasPublicMethod(ReflectionClass $reflectionClass, string $methodName): bool
    {
        if (!$reflectionClass->hasMethod($methodName)) {
            return false;
        }

        return $reflectionClass->getMethod($methodName)->isPublic();
    }

}
