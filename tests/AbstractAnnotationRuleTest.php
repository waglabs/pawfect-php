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

namespace WagLabs\PawfectPHP\Tests;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;
use WagLabs\PawfectPHP\AbstractAnnotationRule;
use WagLabs\PawfectPHP\Annotations\ApplyRule;
use WagLabs\PawfectPHP\Examples\Source\AnnotatedClass;
use WagLabs\PawfectPHP\Examples\Source\PlainClass;
use WagLabs\PawfectPHP\ReflectionClass;

class AbstractAnnotationRuleTest extends TestCase
{
    /** @var AbstractAnnotationRule */
    private $test;

    public function testSupportsTrue()
    {
        $class = new ReflectionClass(
            new SplFileInfo('../examples/Source/AnnotatedClass.php'),
            BetterReflectionClass::createFromName(AnnotatedClass::class),
            [
                ApplyRule::class,
            ]
        );

        self::assertTrue($this->test->supports($class));
    }

    public function testSupportsFalse()
    {
        $class = new ReflectionClass(
            new SplFileInfo('../examples/Source/PlainClass.php'),
            BetterReflectionClass::createFromName(PlainClass::class),
            []
        );

        self::assertFalse($this->test->supports($class));
    }

    protected function setUp(): void
    {
        $this->test = new class () extends AbstractAnnotationRule {
            public function execute(ReflectionClass $reflectionClass)
            {
            }

            public function getName(): string
            {
                return 'test-rule';
            }

            public function getDescription(): string
            {
                return 'This is a test rule.';
            }
        };
    }
}
