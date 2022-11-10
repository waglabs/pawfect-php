<?php

declare(strict_types=1);/*
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

/**
 * Class ResultsAwareRule
 * @package WagLabs\PawfectPHP
 * @author Andrew Breksa <andrew.breksa@wagwalking.com>
 */
interface AnalysisAwareRule extends RuleInterface
{
    /**
     * @param ReflectionClass $reflectionClass
     * @param Analysis|null $analysis
     * @return void
     */
    public function execute(ReflectionClass $reflectionClass, Analysis $analysis = null): void;
}
