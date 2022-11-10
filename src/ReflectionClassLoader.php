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
use Exception;
use ReflectionException;
use Roave\BetterReflection\Reflection\Adapter\ReflectionClass as ReflectionClassAdapter;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use SplFileInfo;

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
        if (array_key_exists(sha1($splFileInfo->getPathname()), $this->fileClassCache)) {
            return $this->fileClassCache[sha1($splFileInfo->getPathname())];
        }

        if (class_exists('\Roave\BetterReflection\Reflector\DefaultReflector')) {
            /** @var array<BetterReflectionClass> $classes */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            /** @psalm-suppress UndefinedClass */
            $classes = (new \Roave\BetterReflection\Reflector\DefaultReflector(
                new SingleFileSourceLocator($splFileInfo->getPathname(), $this->astLocator)
            ))->reflectAllClasses();
        } else {
            /** @var array<BetterReflectionClass> $classes */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            /** @psalm-suppress UndefinedClass */
            $classes = (new \Roave\BetterReflection\Reflector\ClassReflector(
                new SingleFileSourceLocator($splFileInfo->getPathname(), $this->astLocator)
            ))->getAllClasses();
        }

        if (count($classes) !== 1) {
            throw new Exception('unable to load a class in ' . $splFileInfo->getPathname());
        }

        $reflectionClass = $this->loadFromFqn($classes[0]->getName());
        if ($cache) {
            $this->fileClassCache[sha1($splFileInfo->getPathname())] = $reflectionClass;
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
            (new PhpParser())->parseClass(new ReflectionClassAdapter($betterReflectionClass))
        );

        return new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
            $usesNames
        );
    }
}
