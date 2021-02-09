<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Tests\Assertions;


use Mockery;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Assertions\Methods;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class MethodsTest
 * @package WagLabs\PawfectPHP\Tests\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class MethodsTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testHasPublicMethodNoMethod()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(false);
        $rule = new class extends AbstractRule {

            use Methods;

            public function supports(ReflectionClass $reflectionClass): bool
            {
                return false;
            }

            public function execute(ReflectionClass $reflectionClass)
            {
                return;
            }

            public function getName(): string
            {
                return 'test-rule';
            }

            public function getDescription(): string
            {
                return 'test description';
            }
        };

        self::assertFalse($rule->hasPublicMethod($reflectionClass, 'testMethod'));
    }

    public function testHasPublicMethodNotPublic()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(true);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('isPublic')->andReturn(false);
        $reflectionClass->shouldReceive('getMethod')
            ->with('testMethod')
            ->andReturn($reflectionMethod);
        $rule = new class extends AbstractRule {

            use Methods;

            public function supports(ReflectionClass $reflectionClass): bool
            {
                return false;
            }

            public function execute(ReflectionClass $reflectionClass)
            {
                return;
            }

            public function getName(): string
            {
                return 'test-rule';
            }

            public function getDescription(): string
            {
                return 'test description';
            }
        };

        self::assertFalse($rule->hasPublicMethod($reflectionClass, 'testMethod'));
    }

    public function testHasPublicMethodPublic()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('hasMethod')
            ->with('testMethod')
            ->andReturn(true);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('isPublic')->andReturn(true);
        $reflectionClass->shouldReceive('getMethod')
            ->with('testMethod')
            ->andReturn($reflectionMethod);
        $rule = new class extends AbstractRule {

            use Methods;

            public function supports(ReflectionClass $reflectionClass): bool
            {
                return false;
            }

            public function execute(ReflectionClass $reflectionClass)
            {
                return;
            }

            public function getName(): string
            {
                return 'test-rule';
            }

            public function getDescription(): string
            {
                return 'test description';
            }
        };

        self::assertTrue($rule->hasPublicMethod($reflectionClass, 'testMethod'));
    }

}
