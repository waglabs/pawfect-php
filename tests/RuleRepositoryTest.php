<?php declare(strict_types=1);


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

    public function testRegister(){
        $rule = Mockery::mock(RuleInterface::class);
        $ruleRepository = new RuleRepository();
        $ruleRepository->register('test-rule', $rule);
        self::assertEquals($rule, $ruleRepository->getRule('test-rule'));
        self::assertEquals(['test-rule' => $rule], $ruleRepository->getAllRules());
        self::assertEquals(1, $ruleRepository->count());
    }

}
