<?php

namespace WagLabs\PawfectPHP\Tests\Assertions;

use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Required;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Annotations\ApplyRule;
use WagLabs\PawfectPHP\Assertions\Annotation;
use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\Examples\Source\AnnotatedClass;
use WagLabs\PawfectPHP\Examples\Source\PlainClass;
use WagLabs\PawfectPHP\Examples\Source\PoorlyAnnotatedClass;
use WagLabs\PawfectPHP\ReflectionClass;

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

    protected function setUp(): void
    {
        $this->test = new class extends AbstractRule{
            use Annotation;
            public function supports(ReflectionClass $reflectionClass): bool
            {
                return true;
            }

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

    public function testHasAnnotation()
    {
        self::assertTrue($this->test->hasAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testMatchesApplyRuleAnnotation()
    {
        self::assertTrue($this->test->matchesApplyRuleAnnotation($this->annotatedClass, 'any'));
        self::assertFalse($this->test->matchesApplyRuleAnnotation($this->plainClass, 'any'));
    }

    public function testHasClassAnnotation()
    {
        self::assertTrue($this->test->hasClassAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasClassAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testHasPropertyAnnotation()
    {
        self::assertTrue($this->test->hasPropertyAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasPropertyAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testHasMethodAnnotation()
    {
        self::assertTrue($this->test->hasMethodAnnotation($this->annotatedClass));
        self::assertFalse($this->test->hasMethodAnnotation($this->annotatedClass, Attributes::class));
    }

    public function testPoorlyAnnotatedClass()
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
}
