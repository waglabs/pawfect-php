<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP;

use ReflectionException;
use SplFileInfo;

/**
 * Interface ReflectionClassLoaderInterface
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
interface ReflectionClassLoaderInterface
{

    /**
     * @param SplFileInfo $splFileInfo
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function load(SplFileInfo $splFileInfo): ReflectionClass;

    /**
     * @param string $fqn
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function loadFromFqn(string $fqn): ReflectionClass;
}
