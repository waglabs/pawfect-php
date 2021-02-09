<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\FileLoader;


use Generator;
use SplFileInfo;

/**
 * Interface FileLoaderInterface
 * @package WagLabs\PawfectPHP\FileLoader
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
interface FileLoaderInterface
{

    /**
     * @param array<string> $sources
     * @return Generator<SplFileInfo>|array<SplFileInfo>
     */
    public function yieldFiles(array $sources);

    /**
     * @param SplFileInfo $file
     * @return mixed
     */
    public function shouldYieldFile(SplFileInfo $file);
}
