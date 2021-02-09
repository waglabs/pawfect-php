<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP\Tests\FileLoader;


use Mockery;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use WagLabs\PawfectPHP\FileLoader\FileLoader;

/**
 * Class FileLoaderTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class FileLoaderTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testShouldYieldFile()
    {
        $fileLoader = new FileLoader();
        self::assertTrue($fileLoader->shouldYieldFile(new SplFileInfo(__FILE__)));
        self::assertFalse($fileLoader->shouldYieldFile(new SplFileInfo(__DIR__ . '/../composer.json')));
    }

    public function testYieldFiles(){
        $fileLoader = new FileLoader();
        $sources = [
            __FILE__,
            __DIR__ . '/../../src/FileLoader',
            __DIR__ . '/../../thisDoesNotExist'
        ];

        $collected = [];
        $expected = [
            __FILE__,
            realpath(__DIR__ . '/../../src/FileLoader/FileLoader.php'),
            realpath(__DIR__ . '/../../src/FileLoader/FileLoaderInterface.php')
        ];

        foreach ($fileLoader->yieldFiles($sources) as $file){
            $collected[] = $file;
        }

        self::assertSameSize($expected, $collected);
        foreach ($collected as $item){
            self::assertContains($item->getPathName(), $expected);
        }
    }

}
