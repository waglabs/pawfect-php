<?php

namespace WagLabs\PawfectPHP\Tests;

use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;
use WagLabs\PawfectPHP\AbstractAnnotationRule;
use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\Annotations\ApplyRule;
use WagLabs\PawfectPHP\Examples\Source\AnnotatedClass;
use WagLabs\PawfectPHP\Examples\Source\PlainClass;
use WagLabs\PawfectPHP\ReflectionClass;

class AbstractAnnotationRuleTest extends TestCase
{
    /** @var AbstractAnnotationRule */
    private $test;

    protected function setUp(): void
    {
        $this->test = new class extends AbstractAnnotationRule{
            public function execute(ReflectionClass $reflectionClass)
            {}

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
}
