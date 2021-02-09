<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP;

/**
 * Interface RuleInterface
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
interface RuleInterface
{

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool
     */
    public function supports(ReflectionClass $reflectionClass): bool;

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool|void|null
     * @throws FailedAssertionException
     */
    public function execute(ReflectionClass $reflectionClass);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

}
