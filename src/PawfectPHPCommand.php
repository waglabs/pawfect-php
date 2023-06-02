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

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use WagLabs\PawfectPHP\FileLoader\FileLoaderInterface;

/**
 * Class PawfectPHPCommand
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class PawfectPHPCommand extends Command
{
    /**
     * @var string|null
     */
    protected static $defaultName = 'scan';
    /**
     * @var FileLoaderInterface
     */
    protected $fileLoader;
    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRegistry;

    /**
     * @var ReflectionClassLoaderInterface
     */
    protected $reflectionClassLoader;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * PawfectPHPCommand constructor.
     * @param FileLoaderInterface $fileLoader
     * @param RuleRepositoryInterface $ruleRegistry
     * @param ReflectionClassLoaderInterface $reflectionClassLoader
     * @param ContainerInterface $container
     */
    public function __construct(
        FileLoaderInterface $fileLoader,
        RuleRepositoryInterface $ruleRegistry,
        ReflectionClassLoaderInterface $reflectionClassLoader,
        ContainerInterface $container
    ) {
        parent::__construct();
        $this->fileLoader            = $fileLoader;
        $this->ruleRegistry          = $ruleRegistry;
        $this->reflectionClassLoader = $reflectionClassLoader;
        $this->container             = $container;
    }

    protected function configure(): void
    {
        $this->setDescription('Scans application code and runs discovered classes through the provided rules');
        $this->addArgument(
            'rules',
            InputArgument::REQUIRED,
            'The directory to inspect for rules'
        );
        $this->addArgument(
            'paths',
            InputArgument::IS_ARRAY,
            'The list of directories and files to scan'
        );
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
            'If passed, the application will not return with a non-zero exit code if there are any rule failures'
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = time();
        $output->writeln('<fg=green>Wag Labs pawfect-php by Andrew Breksa, Tyson Green, and contributors</>');
        $output->writeln('');
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @psalm-suppress PossiblyInvalidCast */
        foreach ($this->fileLoader->yieldFiles([(string)$input->getArgument('rules')]) as $ruleFile) {
            $symfonyStyle->writeln(
                'inspecting ' . $ruleFile->getPathname() . ' for rules',
                OutputInterface::VERBOSITY_DEBUG
            );
            try {
                $ruleReflectionClass = $this->reflectionClassLoader->load($ruleFile);
            } catch (Throwable $exception) {
                $symfonyStyle->writeln('<fg=red>[!] exception inspecting ' . $ruleFile->getPathname() . ', skipping</>');
                $symfonyStyle->writeln(
                        sprintf('[*] exception inspecting %s: %s', $ruleFile->getPathname(), $exception->getMessage()),
                        OutputInterface::VERBOSITY_DEBUG
                );
                continue;
            }
            if (!$ruleReflectionClass->implementsInterface(RuleInterface::class)) {
                $symfonyStyle->writeln(
                    $ruleReflectionClass->getName() . ' does not implement ' . RuleInterface::class,
                    OutputInterface::VERBOSITY_DEBUG
                );
                continue;
            }

            /**
             * @var RuleInterface $ruleInstance
             */
            $ruleInstance = $this->container->get($ruleReflectionClass->getName());
            $symfonyStyle->writeln(
                'registering ' . $ruleReflectionClass->getName() . ' as a rule',
                OutputInterface::VERBOSITY_DEBUG
            );
            $this->ruleRegistry->register($ruleInstance->getName(), $ruleInstance);
        }

        if ($this->ruleRegistry->count() === 0) {
            $symfonyStyle->error('no rules found');
            return 1;
        }

        $analysis = new Analysis($symfonyStyle);

        $registeredRules = $this->ruleRegistry->getAllRules();
        $analysis->setRegisteredRules(count(array_keys($registeredRules)));
        $appliedRuleNames = [];

        /** @psalm-suppress PossiblyInvalidArgument */
        foreach ($this->fileLoader->yieldFiles($input->getArgument('paths')) as $classFile) {
            $analysis->incrementInspectedFiles();
            $analysis->debug();
            $analysis->debug("inspecting " . $classFile->getPathname() . " for classes");

            try {
                $reflectionClass = $this->reflectionClassLoader->load($classFile);
            } catch (Throwable $exception) {
                $symfonyStyle->writeln('<fg=red>[!] exception inspecting ' . $classFile->getPathname() . ', skipping</>');
                $symfonyStyle->writeln(
                        sprintf('[*] exception inspecting %s: %s', $classFile->getPathname(), $exception->getMessage()),
                        OutputInterface::VERBOSITY_DEBUG
                );
                continue;
            }

            $appliedRules = 0;
            $analysis->incrementInspectedClasses();
            foreach ($registeredRules as $name => $rule) {
                try {
                    if (!$rule->supports($reflectionClass)) {
                        $analysis->debug('class is not supported by rule', $reflectionClass, $rule);
                        continue;
                    }
                } catch (Throwable $throwable) {
                    $symfonyStyle->writeln('<fg=red>[!] exception checking if ' . $classFile->getPathname() . ' is supported by ' . get_class($rule) . ', skipping</>');
                    continue;
                }
                if (0 === $appliedRules++) {
                    $symfonyStyle->newLine();
                    $symfonyStyle->writeln('<options=bold>' . $reflectionClass->getName() . '</>');
                }
                try {
                    $appliedRuleNames[] = $name;
                    if ($rule instanceof AnalysisAwareRule) {
                        $count = $analysis->getFailCount() + $analysis->getExceptionCount();
                        $rule->execute($reflectionClass, $analysis);
                        if ($count === ($analysis->getFailCount() + $analysis->getExceptionCount())) {
                            $analysis->pass($reflectionClass, $rule);
                        }
                    } else {
                        $result = $rule->execute($reflectionClass);
                        if ($result === true || $result === null) {
                            $analysis->pass($reflectionClass, $rule);
                        } else {
                            $analysis->fail($reflectionClass, $rule, $rule->getDescription());
                        }
                    }
                } catch (FailedAssertionException $assertionException) {
                    $analysis->fail($reflectionClass, $rule, $assertionException->getMessage());
                } catch (Throwable $throwable) {
                    $analysis->exception($reflectionClass, $rule, $throwable);
                }
            }

            if ($appliedRules === 0) {
                $analysis->debug('no rules found for class', $reflectionClass);
            }
        }

        $duration = time() - $start;

        $symfonyStyle->newLine();
        $symfonyStyle->writeln(sprintf(
            "<fg=blue>Registered Rules: %s, Inspected Files: %s, Scanned Classes: %s, Applied Rules: %s, Passes: %s, Failures: %s, Exceptions: %s, Warnings: %s, Time: %s</>",
            $analysis->getRegisteredRules(),
            $analysis->getInspectedFiles(),
            $analysis->getInspectedClasses(),
            count(array_unique($appliedRuleNames)),
            $analysis->getPassCount(),
            $analysis->getFailCount(),
            $analysis->getExceptionCount(),
            $analysis->getWarnCount(), 
            sprintf('%02d:%02d:%02d', (int)($duration / 3600), ((int)($duration / 60) % 60), $duration % 60)
        ));

        if ($analysis->getFailCount() > 0) {
            $symfonyStyle->newLine();
            $symfonyStyle->writeln('<fg=red;options=bold>Observed ' . $analysis->getFailCount() . ' failure(s):</>');
            foreach ($analysis->getFailures() as $class => $ruleFailures) {
                $symfonyStyle->writeln('- ' . $class . ':');
                foreach ($ruleFailures as $rule => $failures) {
                    $symfonyStyle->writeln("\t" . '- ' . $rule . ':');
                    foreach ($failures as $failure) {
                        $symfonyStyle->writeln("\t\t" . '<fg=red>- ' . $failure[0]
                            . ($failure[1] !== null ? ' (line ' . $failure[1] . ')' : '')
                            . '</>');
                    }
                }
            }
        }

        if ($analysis->getExceptionCount() > 0) {
            $symfonyStyle->newLine();
            $symfonyStyle->writeln('<fg=red;options=bold>Observed ' . $analysis->getFailCount() . ' exception(s):</>');
            foreach ($analysis->getExceptions() as $class => $ruleExceptions) {
                $symfonyStyle->writeln('- ' . $class . ':');
                foreach ($ruleExceptions as $rule => $exceptions) {
                    $symfonyStyle->writeln("\t" . '- ' . $rule . ':');
                    foreach ($exceptions as $exception) {
                        $symfonyStyle->writeln("\t\t" . '<fg=red>- ' . $exception->getMessage()
                            . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')'
                            . '</>');
                    }
                }
            }
        }

        if ($analysis->getWarnCount() > 0) {
            $symfonyStyle->newLine();
            $symfonyStyle->writeln('<fg=yellow;options=bold>Observed ' . $analysis->getFailCount() . ' warnings(s):</>');
            foreach ($analysis->getWarnings() as $class => $ruleWarnings) {
                $symfonyStyle->writeln('- ' . $class . ':');
                foreach ($ruleWarnings as $rule => $warnings) {
                    $symfonyStyle->writeln("\t" . '- ' . $rule . ':');
                    foreach ($warnings as $warning) {
                        $symfonyStyle->writeln("\t\t" . '<fg=yellow>- ' . $warning[0]
                            . ($warning[1] !== null ? ' (line ' . $warning[1] . ')' : '')
                            . '</>');
                    }
                }
            }
        }

        if ($analysis->getFailCount() > 0 || $analysis->getExceptionCount() > 0) {
            $symfonyStyle->error(sprintf('%s failure(s) and %s exception(s)', $analysis->getFailCount(), $analysis->getExceptionCount()));
        }

        if ($analysis->getFailCount() > 0 || $analysis->getExceptionCount() > 0) {
            if ($input->getOption('dry-run')) {
                return 0;
            }

            return 1;
        }
        $symfonyStyle->success('all rules pass');

        return 0;
    }
}
