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
use Roave\BetterReflection\Reflection\ReflectionMethod;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Assertions\Methods;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class MethodsTest
 * @package WagLabs\PawfectPHP\Tests\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class MethodsTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testHasPublicMethodNoMethod()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(false);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertFalse($rule->hasPublicMethod($reflectionClass, 'testMethod'));
    }

    public function testHasPublicMethodNotPublic()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(true);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('isPublic')->andReturn(false);
        $reflectionClass->shouldReceive('getMethod')
            ->with('testMethod')
            ->andReturn($reflectionMethod);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertFalse($rule->hasPublicMethod($reflectionClass, 'testMethod'));
    }

    public function testHasPublicMethodPublic()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(true);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('isPublic')->andReturn(true);
        $reflectionClass->shouldReceive('getMethod')
            ->with('testMethod')
            ->andReturn($reflectionMethod);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertTrue($rule->hasPublicMethod($reflectionClass, 'testMethod'));
    }

    public function testHasProtectedMethod()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(true);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('isProtected')->andReturn(true);
        $reflectionClass->shouldReceive('getMethod')
            ->with('testMethod')
            ->andReturn($reflectionMethod);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertTrue($rule->hasProtectedMethod($reflectionClass, 'testMethod'));
    }

    public function testHasPrivateMethod()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(true);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('isPrivate')->andReturn(true);
        $reflectionClass->shouldReceive('getMethod')
            ->with('testMethod')
            ->andReturn($reflectionMethod);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertTrue($rule->hasPrivateMethod($reflectionClass, 'testMethod'));
    }

    public function testHasProtectedMethodNoMethod()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(false);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertFalse($rule->hasProtectedMethod($reflectionClass, 'testMethod'));
    }

    public function testHasPrivateMethodNoMethod()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(false);
        $rule = new class () extends AbstractRule {
            use Methods;

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

        self::assertFalse($rule->hasPrivateMethod($reflectionClass, 'testMethod'));
    }
}
