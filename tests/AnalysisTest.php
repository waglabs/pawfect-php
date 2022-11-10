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
use Symfony\Component\Console\Style\SymfonyStyle;
use WagLabs\PawfectPHP\Analysis;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\RuleInterface;

/**
 * Class AnalysisTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class AnalysisTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testWarning()
    {
        $class = Mockery::mock(ReflectionClass::class);
        $class->allows('getName')->andReturn('testClass');

        $symfonyStyle = Mockery::mock(SymfonyStyle::class);
        $symfonyStyle->expects('writeln')->once();

        $analysis = new Analysis($symfonyStyle);
        $analysis->warn(
            $class,
            $rule = Mockery::mock(RuleInterface::class),
            'This is a message'
        );

        self::assertEquals('This is a message', $analysis->getWarnings()['testClass'][get_class($rule)][0][0]);
    }

    public function testGetPasses()
    {
        $class = Mockery::mock(ReflectionClass::class);
        $class->allows('getName')->andReturn('testClass');
        $rule = Mockery::mock(RuleInterface::class);

        $symfonyStyle = Mockery::mock(SymfonyStyle::class);
        $symfonyStyle->expects('writeln')->once();

        $analysis = new Analysis($symfonyStyle);

        $analysis->pass($class, $rule);

        self::assertEquals(get_class($rule), $analysis->getPasses()['testClass'][0]);
    }
}
