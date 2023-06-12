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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Class Analysis
 * @package WagLabs\PawfectPHP
 * @author Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class Analysis
{
    /** @var array<string, array<string, array<mixed>>> */
    protected array $failures = [];
    /** @var array<string, array<string>> */
    protected array $passes = [];
    /** @var array<string, array<string, array<mixed>>> */
    protected array $warnings = [];
    /** @var array<string, array<string, array<Throwable>>> */
    protected array $exceptions = [];
    /** @var int */
    protected int $failCount = 0;
    /** @var int */
    protected $passCount = 0;
    /** @var int */
    protected int $exceptionCount = 0;
    /** @var int */
    protected int $warnCount = 0;
    /** @var int */
    protected $inspectedFiles = 0;
    /** @var int */
    protected $registeredRules = 0;
    /** @var int */
    protected $inspectedClasses = 0;
    /** @var SymfonyStyle */
    private SymfonyStyle $symfonyStyle;

    /**
     * @param SymfonyStyle $symfonyStyle
     */
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }


    /**
     * @param ReflectionClass                 $reflectionClass
     * @param RuleInterface|AnalysisAwareRule $rule
     * @return void
     */
    public function pass(ReflectionClass $reflectionClass, $rule): void
    {
        if (!array_key_exists($reflectionClass->getName(), $this->passes)) {
            $this->passes[$reflectionClass->getName()] = [];
        }
        $this->passes[$reflectionClass->getName()][] = get_class($rule);
        $this->symfonyStyle->writeln('<fg=green>[✓] passed rule ' . get_class($rule) . '</>');
        $this->passCount++;
    }

    /**
     * @param ReflectionClass                 $reflectionClass
     * @param RuleInterface|AnalysisAwareRule $rule
     * @param string                          $message
     * @param int|null                        $line
     * @return void
     */
    public function fail(
            ReflectionClass $reflectionClass,
                            $rule,
            string          $message = null,
            int             $line = null
    ): void {
        $ruleClass = get_class($rule);
        if (!array_key_exists($reflectionClass->getName(), $this->failures)) {
            $this->failures[$reflectionClass->getName()] = [];
        }
        if (!array_key_exists($ruleClass, $this->failures[$reflectionClass->getName()])) {
            $this->failures[$reflectionClass->getName()][$ruleClass] = [];
        }
        $this->failures[$reflectionClass->getName()][$ruleClass][] = [
                $message,
            $line,
        ];
        $this->symfonyStyle->writeln(
            '<fg=red>[x] failure for rule ' . $ruleClass . ': ' . ($message ?? $rule->getDescription())
            . ($line !== null ? ' (line ' . $line . ')' : '') . '</>'
        );
        $this->failCount++;
    }

    /**
     * @param ReflectionClass                 $reflectionClass
     * @param RuleInterface|AnalysisAwareRule $rule
     * @param string                          $message
     * @param int|null                        $line
     * @return void
     */
    public function warn(ReflectionClass $reflectionClass, $rule, string $message, int $line = null): void
    {
        $ruleClass = get_class($rule);
        if (!array_key_exists($reflectionClass->getName(), $this->warnings)) {
            $this->warnings[$reflectionClass->getName()] = [];
        }
        if (!array_key_exists($ruleClass, $this->warnings[$reflectionClass->getName()])) {
            $this->warnings[$reflectionClass->getName()][$ruleClass] = [];
        }
        $this->warnings[$reflectionClass->getName()][$ruleClass][] = [
                $message,
                $line,
        ];
        $this->symfonyStyle->writeln(
            '<fg=yellow>[?] warning while running rule ' . $ruleClass . ': ' . $message
            . ($line !== null ? ' (line ' . $line . ')' : '')
        );
        $this->warnCount++;
    }

    /**
     * @param ReflectionClass                 $reflectionClass
     * @param RuleInterface|AnalysisAwareRule $rule
     * @param Throwable                       $throwable
     * @return void
     */
    public function exception(ReflectionClass $reflectionClass, $rule, Throwable $throwable): void
    {
        $ruleClass = get_class($rule);
        if (!array_key_exists($reflectionClass->getName(), $this->exceptions)) {
            $this->exceptions[$reflectionClass->getName()] = [];
        }
        if (!array_key_exists($ruleClass, $this->exceptions[$reflectionClass->getName()])) {
            $this->exceptions[$reflectionClass->getName()][$ruleClass] = [];
        }
        $this->exceptions[$reflectionClass->getName()][$ruleClass][] = $throwable;
        $this->symfonyStyle->writeln(
            '<fg=red>[!] exception running rule ' . $ruleClass . ': ' . $throwable->getMessage()
        );
        $this->exceptionCount++;
    }

    /**
     * @param string                               $message
     * @param ReflectionClass|null                 $reflectionClass
     * @param RuleInterface|AnalysisAwareRule|null $rule
     * @return void
     */
    public function debug(
            string           $message = '',
            ?ReflectionClass $reflectionClass = null,
                             $rule = null
    ): void {
        if (mb_strlen($message) === 0) {
            $this->symfonyStyle->writeln('', OutputInterface::VERBOSITY_DEBUG);
            return;
        }
        $this->symfonyStyle->writeln(
                sprintf(
                        '<fg=yellow>[*] %s (Class: %s, Rule: %s)',
                        $message,
                        ($reflectionClass !== null ? $reflectionClass->getName() : 'N/A'),
                        ($rule !== null ? get_class($rule) : 'N/A')
                ),
                OutputInterface::VERBOSITY_DEBUG
        );
    }

    /**
     * @return void
     */
    public function incrementInspectedClasses(): void
    {
        $this->inspectedClasses++;
    }

    /**
     * @return void
     */
    public function incrementInspectedFiles(): void
    {
        $this->inspectedFiles++;
    }

    /**
     * @return array<string, array<string, array<mixed>>>
     */
    public function getFailures(): array
    {
        return $this->failures;
    }

    /**
     * @return array<string, array<string>>
     */
    public function getPasses(): array
    {
        return $this->passes;
    }

    /**
     * @return array<string, array<string, array<mixed>>>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return array<string, array<string, array<Throwable>>>
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @return int
     */
    public function getFailCount(): int
    {
        return $this->failCount;
    }

    /**
     * @return int
     */
    public function getPassCount(): int
    {
        return $this->passCount;
    }

    /**
     * @return int
     */
    public function getExceptionCount(): int
    {
        return $this->exceptionCount;
    }

    /**
     * @return int
     */
    public function getWarnCount(): int
    {
        return $this->warnCount;
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
    public function getInspectedClasses(): int
    {
        return $this->inspectedClasses;
    }
}
