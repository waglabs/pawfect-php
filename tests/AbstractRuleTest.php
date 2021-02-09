<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Tests;


use PHPUnit\Framework\TestCase;
use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class AbstractRuleTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class AbstractRuleTest extends TestCase
{

    public function testAssert()
    {
        $class = new class extends AbstractRule {

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

        self::expectExceptionMessage('Test Message');
        $class->assert(false, 'Test Message');
    }

    public function testAssertPasses()
    {
        $rule = new class extends AbstractRule {

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

        $rule->assert(true, 'Test Message');
        self::assertTrue(true);
    }

}
