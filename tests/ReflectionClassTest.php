<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Tests;


use Mockery;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;
use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Class ReflectionClassTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ReflectionClassTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test__call()
    {
        $splFileInfo = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $betterReflectionClass->shouldReceive('getName')->andReturn(ReflectionClassTest::class)->once();
        $reflectionClass = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
        );

        self::assertEquals(ReflectionClassTest::class, $reflectionClass->getName());
    }

    public function testGetUses()
    {
        $splFileInfo = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $reflectionClass = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
            [
                ReflectionClass::class
            ]
        );

        self::assertEquals([ReflectionClass::class], $reflectionClass->getUses());
    }

    public function testGetSplFileInfo()
    {
        $splFileInfo = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $reflectionClass = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass
        );

        self::assertEquals($splFileInfo, $reflectionClass->getSplFileInfo());
    }

    public function testGetReflectionClass()
    {
        $splFileInfo = Mockery::mock(SplFileInfo::class);
        $betterReflectionClass = Mockery::mock(BetterReflectionClass::class);
        $reflectionClass = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass
        );

        self::assertEquals($betterReflectionClass, $reflectionClass->getReflectionClass());
    }
}
