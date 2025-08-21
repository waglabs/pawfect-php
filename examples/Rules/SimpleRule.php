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
use WagLabs\PawfectPHP\Analysis;
use WagLabs\PawfectPHP\AnalysisAwareRule;
use WagLabs\PawfectPHP\Assertions\Methods;
use WagLabs\PawfectPHP\FailedAssertionException;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class SimpleRule
 * @package WagLabs\PawfectPHP\Examples\Rules
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class SimpleRule extends AbstractRule implements AnalysisAwareRule
{
    use Methods;

    public function supports(ReflectionClass $reflectionClass): bool
    {
        return $reflectionClass->hasMethod('doSomething');
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return bool|void
     * @throws FailedAssertionException
     */
    public function execute(ReflectionClass $reflectionClass, Analysis $analysis = null): void
    {
        if (!$this->hasPublicMethod($reflectionClass, '__construct')) {
            $analysis->fail($reflectionClass, $this, 'Class does not have a __construct method');
        }
        $analysis->exception($reflectionClass, $this, new \Exception('test message'));
        $analysis->warn($reflectionClass, $this, 'Testing...');
        $analysis->warn($reflectionClass, $this, 'Testing...');
        $analysis->pass($reflectionClass, $this);
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
