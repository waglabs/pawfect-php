<?php

declare(strict_types=1);
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

namespace WagLabs\PawfectPHP\Tests;

use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class AbstractRuleTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class AbstractRuleTest extends TestCase
{
    public function testAssert()
    {
        $class = new class() extends AbstractRule {
            public function supports(ReflectionClass $reflectionClass): bool
            {
                return false;
            }

            public function execute(ReflectionClass $reflectionClass)
            {
            }

            public function getName(): string
            {
                return 'test-rule';
            }

            public function getDescription(): string
            {
                return 'test description';
            }
        };

        self::expectExceptionMessage('Test Message');
        $class->assert(false, 'Test Message');
    }

    public function testAssertPasses()
    {
        $rule = new class() extends AbstractRule {
            public function supports(ReflectionClass $reflectionClass): bool
            {
                return false;
            }

            public function execute(ReflectionClass $reflectionClass)
            {
            }

            public function getName(): string
            {
                return 'test-rule';
            }

            public function getDescription(): string
            {
                return 'test description';
            }
        };

        $rule->assert(true, 'Test Message');
        self::assertTrue(true);
    }
}
