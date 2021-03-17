<?php
/*
 * This file is part of waglabs/pawfect-php.
 *
 * (C) 2021 Wag Labs, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace WagLabs\PawfectPHP\Examples\Rules;

use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\FileLoader\FileLoaderInterface;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\ReflectionClassLoaderInterface;
use WagLabs\PawfectPHP\RuleRepository;
use WagLabs\PawfectPHP\RuleRepositoryInterface;

/**
 * Class ComplexRule
 * @package WagLabs\PawfectPHP\Examples\Rules
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ComplexRule extends AbstractRule
{
    /**
     * @var ReflectionClassLoaderInterface
     */
    protected $reflectionClassLoader;

    /**
     * ComplexRule constructor.
     * @param ReflectionClassLoaderInterface $reflectionClassLoader
     */
    public function __construct(ReflectionClassLoaderInterface $reflectionClassLoader)
    {
        $this->reflectionClassLoader = $reflectionClassLoader;
    }


    public function supports(ReflectionClass $reflectionClass): bool
    {
        return
            $reflectionClass->implementsInterface(FileLoaderInterface::class)
            ||
            $reflectionClass->implementsInterface(RuleRepositoryInterface::class);
    }


    public function execute(ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->implementsInterface(FileLoaderInterface::class)) {
            $this->assert($reflectionClass->hasMethod('yieldFiles'));
            $this->assert(!$reflectionClass->getMethod('yieldFiles')->hasReturnType());
            return;
        }

        if ($reflectionClass->getName() === RuleRepository::class) {
            $this->assert($reflectionClass->hasProperty('rules'));
            $this->assert($reflectionClass->getProperty('rules')->isProtected(), 'Rules property is not protected');
        }
    }

    public function getName(): string
    {
        return 'complex-rule';
    }

    public function getDescription(): string
    {
        return 'a complex rule to demonstrate advanced functionality';
    }
}
