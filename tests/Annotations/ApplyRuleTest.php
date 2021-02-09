<?php

namespace WagLabs\PawfectPHP\Tests\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use WagLabs\PawfectPHP\Annotations\ApplyRule;
use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\Examples\Source\AnnotatedClass;

class ApplyRuleTest extends TestCase
{

    public function test__construct()
    {
        $test = new ApplyRule([]);
        self::assertIsArray($test->names);
        self::assertNull($test->regex);
    }

    public function test__constructFull()
    {
        $test = new ApplyRule([
            'value' => 'test-override',
            'names' => [
                'this-is-overridden-by-primary-value',
                'THis can technically be any string',
            ],
            'regex' => '/.*/'
        ]);
        self::assertIsArray($test->names);
        self::assertTrue(in_array('test-override', $test->names));
        self::assertIsString($test->regex);
    }

    public function testMatchesAlways()
    {
        $test = new ApplyRule([]);
        self::assertTrue($test->matches(random_bytes(10)));
    }

    public function testMatchesRegex()
    {
        $test = new ApplyRule(['regex' => '/.*/']);
        self::assertTrue($test->matches(random_bytes(10)));
    }

    public function testMatchesExact()
    {
        $name = random_bytes(10);
        $test = new ApplyRule(['value' => $name]);
        self::assertTrue($test->matches($name));
    }

    public function testReading()
    {
        // Expected annotations
        // @see \Wag\ApplyRule\Examples\Source\AnnotatedClass
        /*
         * @ApplyRule
         * @ApplyRule("single-rule")
         * @ApplyRule({"rule-1", "rule-2"})
         * @ApplyRule(names={"rule-1", "rule-2"})
         * @ApplyRule(names="invalid")
         * @ApplyRule(regex="/^starts-with-/")
         * @ApplyRule(regex="invalid")
         * @ApplyRule(names={"rule-1", "rule-2"}, regex="/^won't-be-tested/")
         * @ApplyRule("override", names={"rule-1", "rule-2"}, regex="/^this-either/")
         */
        $reader = new AnnotationReader();
        $annotations = $reader->getClassAnnotations(new ReflectionClass(AnnotatedClass::class));
        $annotation = array_shift($annotations);
        self::assertEmpty($annotation->names);
        $annotation = array_shift($annotations);
        self::assertCount(1, $annotation->names);
        $annotation = array_shift($annotations);
        self::assertCount(2, $annotation->names);
        $annotation = array_shift($annotations);
        self::assertCount(2, $annotation->names);
        $annotation = array_shift($annotations);
        self::assertIsString($annotation->names); // This will fail ->matches()
        $annotation = array_shift($annotations);
        self::assertEquals('/^starts-with-/', $annotation->regex);
        $annotation = array_shift($annotations);
        self::assertEquals('invalid', $annotation->regex); // Not valid pattern
        $annotation = array_shift($annotations);
        self::assertTrue($annotation->matches('rule-1'));
        self::assertFalse($annotation->matches('won\'t-be-tested'));
        $annotation = array_shift($annotations);
        self::assertTrue($annotation->matches('override'));
        self::assertFalse($annotation->matches('rule-1'));
    }
}
