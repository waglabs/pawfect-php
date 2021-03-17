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

namespace WagLabs\PawfectPHP\Assertions;

use WagLabs\PawfectPHP\ReflectionClass;

/**
 * Trait Hierarchy
 * @package WagLabs\PawfectPHP\Assertions
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
trait Hierarchy
{
    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $interface
     * @return bool
     */
    public function implements(ReflectionClass $reflectionClass, string $interface): bool
    {
        return $reflectionClass->implementsInterface($interface);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $parent
     * @return bool
     */
    public function extendsFrom(ReflectionClass $reflectionClass, string $parent): bool
    {
        if ($reflectionClass->isInterface()) {
            return in_array($parent, $reflectionClass->getInterfaceNames());
        }

        return in_array($parent, $reflectionClass->getParentClassNames());
    }
}
