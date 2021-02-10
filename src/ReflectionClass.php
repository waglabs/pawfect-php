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

namespace WagLabs\PawfectPHP;


use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use SplFileInfo;

/**
 * Class ReflectionClass
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 * @mixin BetterReflectionClass
 */
class ReflectionClass
{

    /**
     * @var array<string>
     */
    protected $uses;

    /**
     * @var BetterReflectionClass
     */
    protected $betterReflectionClass;

    /**
     * @var SplFileInfo|null
     */
    protected $splFileInfo;

    /**
     * ClassInfo constructor.
     * @param SplFileInfo|null      $splFileInfo
     * @param BetterReflectionClass $betterReflectionClass
     * @param array<string>         $uses
     */
    public function __construct(
        ?SplFileInfo $splFileInfo,
        BetterReflectionClass $betterReflectionClass,
        array $uses = []
    ) {
        $this->splFileInfo = $splFileInfo;
        $this->betterReflectionClass = $betterReflectionClass;
        $this->uses = $uses;
    }

    /**
     * @return BetterReflectionClass
     */
    public function getReflectionClass(): BetterReflectionClass
    {
        return $this->betterReflectionClass;
    }

    /**
     * @param string       $name
     * @param array<mixed> $arguments
     * @return false|mixed
     */
    public function __call(string $name, array $arguments)
    {
        /**
         * @var callable $callable
         */
        $callable = [$this->betterReflectionClass, $name];
        return call_user_func_array($callable, $arguments);
    }

    /**
     * @return array<string>
     */
    public function getUses(): array
    {
        return $this->uses;
    }

    /**
     * @return SplFileInfo|null
     */
    public function getSplFileInfo(): ?SplFileInfo
    {
        return $this->splFileInfo;
    }
}
