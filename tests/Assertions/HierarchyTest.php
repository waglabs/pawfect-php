<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Tests\Assertions;


use Mockery;
use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\Assertions\Hierarchy;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\RuleInterface;

/**
 * Class HierarchyTest
 * @package WagLabs\PawfectPHP\Tests\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class HierarchyTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testImplements()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $rule = new class extends AbstractRule {

            use Hierarchy;

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

        self::assertTrue($rule->implements($reflectionClass, RuleInterface::class));
    }

    public function testDoesNotImplement()
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(false)
            ->once();
        $rule = new class extends AbstractRule {

            use Hierarchy;

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

        self::assertFalse($rule->implements($reflectionClass, RuleInterface::class));
    }

}
