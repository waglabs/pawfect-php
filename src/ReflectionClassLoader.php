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

namespace WagLabs\PawfectPHP;

use Doctrine\Common\Annotations\PhpParser;
use ReflectionException;
use Roave\BetterReflection\Reflection\Adapter\ReflectionClass as ReflectionClassAdapter;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use Roave\BetterReflection\Reflector\DefaultReflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use RuntimeException;
use SplFileInfo;
use WagLabs\PawfectPHP\Exceptions\NoSupportedClassesFoundInFile;

/**
 * Class ReflectionClassLoader
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ReflectionClassLoader implements ReflectionClassLoaderInterface
{
    /**
     * @var Locator
     */
    protected $astLocator;

    /**
     * @var array<string, ReflectionClass>
     */
    protected $fileClassCache = [];

    /**
     * ReflectionClassLoader constructor.
     * @param Locator $astLocator
     */
    public function __construct(Locator $astLocator)
    {
        $this->astLocator = $astLocator;
    }


    /**
     * @param SplFileInfo $splFileInfo
     * @param bool $cache
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function load(SplFileInfo $splFileInfo, bool $cache = true): ReflectionClass
    {
        $pathname = $splFileInfo->getPathname();

        if (empty($pathname)) {
            throw new RuntimeException('provided SplFileInfo has an empty pathname');
        }

        if (array_key_exists(sha1($pathname), $this->fileClassCache)) {
            return $this->fileClassCache[sha1($pathname)];
        }

        /** @var array<int, BetterReflectionClass> $classes */
        $classes = (new DefaultReflector(
            new SingleFileSourceLocator($pathname, $this->astLocator)
        ))->reflectAllClasses();

        $supportedClasses = [];
        foreach ($classes as $class) {
            if ($class->isAnonymous()) {
                continue;
            }
            $supportedClasses[] = $class;
        }
        $classes = $supportedClasses;

        if (count($classes) !== 1) {
            throw new NoSupportedClassesFoundInFile('unable to load a single named class from ' . $pathname);
        }

        $reflectionClass = $this->loadFromFqn($classes[0]->getName());
        if ($cache) {
            $this->fileClassCache[sha1($pathname)] = $reflectionClass;
        }
        return $reflectionClass;
    }

    /**
     * @param string $fqn
     * @param SplFileInfo|null $splFileInfo
     * @return ReflectionClass
     * @throws ReflectionException
     * @psalm-suppress DeprecatedMethod
     */
    public function loadFromFqn(string $fqn, ?SplFileInfo $splFileInfo = null): ReflectionClass
    {
        $betterReflectionClass = BetterReflectionClass::createFromName($fqn);

        $usesNames = array_values(
            (new PhpParser())->parseUseStatements(new ReflectionClassAdapter($betterReflectionClass))
        );

        return new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
            $usesNames
        );
    }
}
