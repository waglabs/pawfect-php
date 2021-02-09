<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\FileLoader;


use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class FileLoader
 * @package WagLabs\PawfectPHP\FileLoader
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class FileLoader implements FileLoaderInterface
{

    /**
     * @param array<string> $sources
     * @return Generator<SplFileInfo>|array<SplFileInfo>
     */
    public function yieldFiles(array $sources)
    {
        foreach ($sources as $source) {
            $realSource = realpath($source);
            if ($realSource === false) {
                continue;
            }

            if (is_dir($realSource)) {
                /** @var iterable<SplFileInfo> $directoryIterator */
                $directoryIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realSource));
                foreach ($directoryIterator as $file) {
                    if ($file->isDir()) {
                        continue;
                    }
                    if ($this->shouldYieldFile($file)) {
                        yield $file;
                    }
                }
            }

            $file = new SplFileInfo($realSource);

            if ($this->shouldYieldFile($file)) {
                yield $file;
            }
        }
    }

    /**
     * @param SplFileInfo $file
     * @return bool
     */
    public function shouldYieldFile(SplFileInfo $file): bool
    {
        if (strpos($file->getPathname(), '.php') === false) {
            return false;
        }

        return true;
    }

}
