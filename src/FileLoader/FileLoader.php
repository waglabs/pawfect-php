<?php

declare(strict_types=1);
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
final class FileLoader implements FileLoaderInterface
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
        if (!str_contains($file->getPathname(), '.php')) {
            return false;
        }

        return true;
    }
}
