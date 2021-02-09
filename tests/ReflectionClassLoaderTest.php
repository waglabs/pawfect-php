<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Tests;


use Mockery;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\BetterReflection;
use SplFileInfo;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\ReflectionClassLoader;

/**
 * Class ReflectionClassLoaderTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ReflectionClassLoaderTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLoad()
    {
        $locator = (new BetterReflection())->astLocator();
        $reflectionClassLoader = new ReflectionClassLoader($locator);

        $splFileInfo = new SplFileInfo(__FILE__);

        $reflectionClass = $reflectionClassLoader->load($splFileInfo);

        self::assertInstanceOf(ReflectionClass::class, $reflectionClass);

        $reflectionClass = $reflectionClassLoader->load($splFileInfo);

        self::assertInstanceOf(ReflectionClass::class, $reflectionClass);

        self::assertContains('PHPUnit\Framework\TestCase', $reflectionClass->getUses());
    }

    public function testLoadNoClasses()
    {
        $locator = (new BetterReflection())->astLocator();
        $reflectionClassLoader = new ReflectionClassLoader($locator);

        $splFileInfo = new SplFileInfo(__DIR__ . '/../bin/pawfect-php');

        self::expectExceptionMessage('unable to load a class in ' . __DIR__ . '/../bin/pawfect-php');

        $reflectionClassLoader->load($splFileInfo);
    }

}
