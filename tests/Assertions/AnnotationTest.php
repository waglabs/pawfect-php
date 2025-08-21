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

namespace WagLabs\PawfectPHP\Tests\Assertions;

use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Required;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Annotations\ApplyRule;
use WagLabs\PawfectPHP\Assertions\Annotation;
use WagLabs\PawfectPHP\Examples\Source\AnnotatedClass;
use WagLabs\PawfectPHP\Examples\Source\PlainClass;
use WagLabs\PawfectPHP\Examples\Source\PoorlyAnnotatedClass;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class AnnotationTest
 * @package WagLabs\PawfectPHP\Tests\Assertions
 */
class AnnotationTest extends TestCase
{
    /**
     * @var AbstractRule|Annotation
     */
    private $test;
    /**
     * @var ReflectionClass
     */
    private $annotatedClass;
    /**
     * @var ReflectionClass
     */
    private $plainClass;

    public function testHasAnnotation(): void
    {
        self::assertTrue($this->test->hasAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testMatchesApplyRuleAnnotation(): void
    {
        self::assertTrue($this->test->matchesApplyRuleAnnotation($this->annotatedClass, 'any'));
        self::assertFalse($this->test->matchesApplyRuleAnnotation($this->plainClass, 'any'));
    }

    public function testHasClassAnnotation(): void
    {
        self::assertTrue($this->test->hasClassAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasClassAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testHasPropertyAnnotation(): void
    {
        self::assertTrue($this->test->hasPropertyAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasPropertyAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testHasMethodAnnotation(): void
    {
        self::assertTrue($this->test->hasMethodAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasMethodAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testPoorlyAnnotatedClass(): void
    {
        // While this class does have an annotation, it has more bad ones than the reader is willing to tolerate
        self::assertFalse($this->test->hasAnnotation(new ReflectionClass(
            new SplFileInfo('../../examples/Source/PoorlyAnnotatedClass.php'),
            BetterReflectionClass::createFromName(PoorlyAnnotatedClass::class),
            [
                ApplyRule::class,
            ]
        )));
    }

    protected function setUp(): void
    {
        $this->test = new class () extends AbstractRule {
            use Annotation;

            public function supports(ReflectionClass $reflectionClass): bool
            {
                return true;
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
                return 'This is a test rule.';
            }
        };
        $this->annotatedClass = new ReflectionClass(
            new SplFileInfo('../../examples/Source/AnnotatedClass.php'),
            BetterReflectionClass::createFromName(AnnotatedClass::class),
            [
                ApplyRule::class,
                Required::class,
            ]
        );
        $this->plainClass = new ReflectionClass(
            new SplFileInfo('../../examples/Source/PlainClass.php'),
            BetterReflectionClass::createFromName(PlainClass::class),
            []
        );
    }
}
