<?php declare(strict_types=1);
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
use WagLabs\PawfectPHP\RuleInterface;
use WagLabs\PawfectPHP\RuleRepository;

/**
 * Class RuleRepositoryTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class RuleRepositoryTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRegister()
    {
        $rule = Mockery::mock(RuleInterface::class);
        $ruleRepository = new RuleRepository();
        $ruleRepository->register('test-rule', $rule);
        self::assertEquals($rule, $ruleRepository->getRule('test-rule'));
        self::assertEquals(['test-rule' => $rule], $ruleRepository->getAllRules());
        self::assertEquals(1, $ruleRepository->count());
    }

}
