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

/**
 * Class RuleRepository
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var array<string, RuleInterface|AnalysisAwareRule>
     */
    protected $rules = [];

    /**
     * @param string $name
     * @return RuleInterface|AnalysisAwareRule
     */
    public function getRule(string $name): mixed
    {
        return $this->rules[$name];
    }

    /**
     * @return array<string, RuleInterface|AnalysisAwareRule>
     */
    public function getAllRules(): array
    {
        return $this->rules;
    }

    /**
     * @param string                          $name
     * @param RuleInterface|AnalysisAwareRule $rule
     */
    public function register(string $name, $rule): void
    {
        $this->rules[$name] = $rule;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->rules);
    }
}
