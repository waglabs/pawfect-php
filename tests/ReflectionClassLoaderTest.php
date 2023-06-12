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
use Roave\BetterReflection\BetterReflection;
use SplFileInfo;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\ReflectionClassLoader;

/**
 * Class ReflectionClassLoaderTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ReflectionClassLoaderTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLoad()
    {
        $locator               = (new BetterReflection())->astLocator();
        $reflectionClassLoader = new ReflectionClassLoader($locator);

        $splFileInfo = new SplFileInfo(__FILE__);

        $reflectionClass = $reflectionClassLoader->load($splFileInfo);

        self::assertInstanceOf(ReflectionClass::class, $reflectionClass);

        $reflectionClass = $reflectionClassLoader->load($splFileInfo);

        self::assertInstanceOf(ReflectionClass::class, $reflectionClass);

        self::assertContains('PHPUnit\Framework\TestCase', $reflectionClass->getUses());
    }

    public function testLoadNoCache()
    {
        $locator               = (new BetterReflection())->astLocator();
        $reflectionClassLoader = new ReflectionClassLoader($locator);

        $splFileInfo = new SplFileInfo(__FILE__);

        $reflectionClass = $reflectionClassLoader->load($splFileInfo);

        self::assertInstanceOf(ReflectionClass::class, $reflectionClass);

        $reflectionClass = $reflectionClassLoader->load($splFileInfo, false);

        self::assertInstanceOf(ReflectionClass::class, $reflectionClass);

        self::assertContains('PHPUnit\Framework\TestCase', $reflectionClass->getUses());
    }

    public function testLoadNoClasses()
    {
        $locator               = (new BetterReflection())->astLocator();
        $reflectionClassLoader = new ReflectionClassLoader($locator);

        $splFileInfo = new SplFileInfo(__DIR__ . '/../bin/pawfect-php');

        $this->expectExceptionMessage('unable to load a single named class from ' . __DIR__ . '/../bin/pawfect-php');

        $reflectionClassLoader->load($splFileInfo);
    }
}
