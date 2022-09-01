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
use WagLabs\PawfectPHP\Results;
use WagLabs\PawfectPHP\RuleInterface;

/**
 * Class ResultsTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ResultsTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testSetRegisteredRules()
    {
        $results = new Results();
        self::assertEquals(0, $results->getRegisteredRules());
        $results->setRegisteredRules(1);
        self::assertEquals(1, $results->getRegisteredRules());
    }


    public function testIncrementFailures()
    {
        $results = new Results();
        self::assertEquals(0, $results->getFailures());
        $results->incrementFailures();
        self::assertEquals(1, $results->getFailures());
    }

    public function testIncrementPasses()
    {
        $results = new Results();
        self::assertEquals(0, $results->getPasses());
        $results->incrementPasses();
        self::assertEquals(1, $results->getPasses());
    }

    public function testIncrementErrors()
    {
        $results = new Results();
        self::assertEquals(0, $results->getErrors());
        $results->incrementErrors();
        self::assertEquals(1, $results->getErrors());
    }

    public function testIncrementScannedClasses()
    {
        $results = new Results();
        self::assertEquals(0, $results->getScannedClasses());
        $results->incrementScannedClasses();
        self::assertEquals(1, $results->getScannedClasses());
    }

    public function testIncrementInspectedFiles()
    {
        $results = new Results();
        self::assertEquals(0, $results->getInspectedFiles());
        $results->incrementInspectedFiles();
        self::assertEquals(1, $results->getInspectedFiles());
    }

    public function testLogFailure()
    {
        $results = new Results();
        $rule    = Mockery::mock(RuleInterface::class);
        $rule->expects('getName')->andReturns('test-rule')->twice();
        $rule->expects('getDescription')->andReturns('test description')->twice();
        $results->logFailure(
            'TestClass1',
            $rule,
            'This is a message'
        );
        $results->logFailure(
            'TestClass2',
            $rule,
            'This is a message'
        );

        self::assertEquals(
            [
                [
                    'TestClass1',
                    'test-rule',
                    'test description',
                    'This is a message',
                ],
                [
                    'TestClass2',
                    'test-rule',
                    'test description',
                    'This is a message',
                ],
            ],
            $results->getFailureArray()
        );
    }

    public function testLogException()
    {
        $results = new Results();
        $rule    = Mockery::mock(RuleInterface::class);
        $rule->expects('getName')->andReturns('test-rule')->twice();
        $rule->expects('getDescription')->andReturns('test description')->twice();
        $results->logException(
            'TestClass1',
            $rule,
            'This is a message'
        );
        $results->logException(
            'TestClass2',
            $rule,
            'This is a message'
        );

        self::assertEquals(
            [
                [
                    'TestClass1',
                    'test-rule',
                    'test description',
                    'This is a message',
                ],
                [
                    'TestClass2',
                    'test-rule',
                    'test description',
                    'This is a message',
                ],
            ],
            $results->getErrorArray()
        );
    }
}
