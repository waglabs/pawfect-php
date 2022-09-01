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

namespace WagLabs\PawfectPHP\Tests\Assertions;

use Mockery;
use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Assertions\Hierarchy;
use WagLabs\PawfectPHP\FileLoader\FileLoader;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\RuleInterface;

/**
 * Class HierarchyTest
 * @package WagLabs\PawfectPHP\Tests\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class HierarchyTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testExtendsFrom()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('isInterface')->andReturn(false)->once();
        $reflectionClass->shouldReceive('getParentClassNames')
            ->andReturn([
                FileLoader::class,
            ])
            ->once();
        $rule = new class () extends AbstractRule {
            use Hierarchy;

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

        self::assertFalse($rule->extendsFrom($reflectionClass, RuleInterface::class));
    }

    public function testExtendsFromInterface()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('isInterface')->andReturn(true)->once();
        $reflectionClass->shouldReceive('getInterfaceNames')
            ->andReturn([
                FileLoader::class,
            ])
            ->once();
        $rule = new class () extends AbstractRule {
            use Hierarchy;

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

        self::assertFalse($rule->extendsFrom($reflectionClass, RuleInterface::class));
    }

    public function testImplements()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $rule = new class () extends AbstractRule {
            use Hierarchy;

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

        self::assertTrue($rule->implements($reflectionClass, RuleInterface::class));
    }

    public function testDoesNotImplement()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(false)
            ->once();
        $rule = new class () extends AbstractRule {
            use Hierarchy;

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

        self::assertFalse($rule->implements($reflectionClass, RuleInterface::class));
    }
}
