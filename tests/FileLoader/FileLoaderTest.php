<?php declare(strict_types=1);
/*
 * This file is part of waglabs/pawfect-php.
 *
 * (C) 2021 Wag Labs, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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

    public function testYieldFiles()
    {
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

        foreach ($fileLoader->yieldFiles($sources) as $file) {
            $collected[] = $file;
        }

        self::assertSameSize($expected, $collected);
        foreach ($collected as $item) {
            self::assertContains($item->getPathName(), $expected);
        }
    }

}
