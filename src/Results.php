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
 * Class Results
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class Results
{
    /**
     * @var int
     */
    protected $failures = 0;

    /**
     * @var int
     */
    protected $passes = 0;

    /**
     * @var int
     */
    protected $errors = 0;

    /**
     * @var int
     */
    protected $scannedClasses = 0;

    /**
     * @var int
     */
    protected $registeredRules = 0;

    /**
     * @var int
     */
    protected $inspectedFiles = 0;

    /**
     * @var array<array>
     */
    protected $failureArray = [];

    /**
     * @var array<array>
     */
    protected $errorArray = [];

    /**
     * @return void
     */
    public function incrementFailures(): void
    {
        $this->failures++;
    }

    /**
     * @return void
     */
    public function incrementPasses(): void
    {
        $this->passes++;
    }

    /**
     * @return void
     */
    public function incrementErrors(): void
    {
        $this->errors++;
    }

    /**
     * @return void
     */
    public function incrementScannedClasses(): void
    {
        $this->scannedClasses++;
    }

    /**
     * @return void
     */
    public function incrementInspectedFiles(): void
    {
        $this->inspectedFiles++;
    }

    /**
     * @param string $className
     * @param RuleInterface $rule
     * @param string|null $message
     */
    public function logFailure(string $className, RuleInterface $rule, string $message = null): void
    {
        $this->failureArray[] = [
            $className,
            $rule->getName(),
            $rule->getDescription(),
            $message
        ];
    }

    /**
     * @param string        $className
     * @param RuleInterface $rule
     * @param string|null   $message
     */
    public function logException(string $className, RuleInterface $rule, string $message = null): void
    {
        $this->errorArray[] = [
            $className,
            $rule->getName(),
            $rule->getDescription(),
            $message
        ];
    }

    /**
     * @return int
     */
    public function getFailures(): int
    {
        return $this->failures;
    }

    /**
     * @return int
     */
    public function getPasses(): int
    {
        return $this->passes;
    }

    /**
     * @return array<array>
     */
    public function getFailureArray(): array
    {
        return $this->failureArray;
    }

    /**
     * @return int
     */
    public function getScannedClasses(): int
    {
        return $this->scannedClasses;
    }

    /**
     * @return int
     */
    public function getRegisteredRules(): int
    {
        return $this->registeredRules;
    }

    /**
     * @param int $registeredRules
     */
    public function setRegisteredRules(int $registeredRules): void
    {
        $this->registeredRules = $registeredRules;
    }

    /**
     * @return int
     */
    public function getInspectedFiles(): int
    {
        return $this->inspectedFiles;
    }

    /**
     * @return int
     */
    public function getErrors(): int
    {
        return $this->errors;
    }

    /**
     * @return array[]
     */
    public function getErrorArray(): array
    {
        return $this->errorArray;
    }
}
