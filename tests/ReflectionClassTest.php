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

use Mockery;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class ReflectionClassTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ReflectionClassTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test__call(): void
    {
        $splFileInfo           = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $betterReflectionClass->shouldReceive('getName')->andReturn(ReflectionClassTest::class)->once();
        $reflectionClass = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
        );

        self::assertEquals(ReflectionClassTest::class, $reflectionClass->getName());
    }

    public function testGetUses(): void
    {
        $splFileInfo           = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $reflectionClass       = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
            [
                ReflectionClass::class,
            ]
        );

        self::assertEquals([ReflectionClass::class], $reflectionClass->getUses());
    }

    public function testGetSplFileInfo(): void
    {
        $splFileInfo           = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $reflectionClass       = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass
        );

        self::assertEquals($splFileInfo, $reflectionClass->getSplFileInfo());
    }

    public function testGetReflectionClass(): void
    {
        $splFileInfo           = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $reflectionClass       = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass
        );

        self::assertEquals($betterReflectionClass, $reflectionClass->getReflectionClass());
    }
}
