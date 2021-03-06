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
use Symfony\Component\Console\Helper\Table;
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
     * @param FileLoaderInterface            $fileLoader
     * @param RuleRepositoryInterface        $ruleRegistry
     * @param ReflectionClassLoaderInterface $reflectionClassLoader
     * @param ContainerInterface             $container
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=green>Wag Labs pawfect-php by Andrew Breksa, Tyson Green, and contributors</>');
        $output->writeln('');
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @psalm-suppress PossiblyInvalidCast */
        foreach ($this->fileLoader->yieldFiles([(string)$input->getArgument('rules')]) as $ruleFile) {
            $output->writeln(
                'inspecting ' . $ruleFile->getPathname() . ' for rules',
                OutputInterface::VERBOSITY_DEBUG
            );
            try {
                $ruleReflectionClass = $this->reflectionClassLoader->load($ruleFile);
            } catch (Throwable $exception) {
                $output->writeln('<fg=red>[!] exception inspecting ' . $ruleFile->getPathname() . ', skipping</>');
                continue;
            }
            if (!$ruleReflectionClass->implementsInterface(RuleInterface::class)) {
                $output->writeln(
                    $ruleReflectionClass->getName() . ' does not implement ' . RuleInterface::class,
                    OutputInterface::VERBOSITY_DEBUG
                );
                continue;
            }

            /**
             * @var RuleInterface $ruleInstance
             */
            $ruleInstance = $this->container->get($ruleReflectionClass->getName());
            $output->writeln(
                'registering ' . $ruleReflectionClass->getName() . ' as a rule',
                OutputInterface::VERBOSITY_DEBUG
            );
            $this->ruleRegistry->register($ruleInstance->getName(), $ruleInstance);
        }

        if ($this->ruleRegistry->count() === 0) {
            $symfonyStyle->error('no rules found');
            return 1;
        }

        $results = new Results();

        /** @psalm-suppress PossiblyInvalidArgument */
        foreach ($this->fileLoader->yieldFiles($input->getArgument('paths')) as $classFile) {
            $output->writeln(
                'inspecting ' . $classFile->getPathname() . ' for classes',
                OutputInterface::VERBOSITY_DEBUG
            );

            try {
                $reflectionClass = $this->reflectionClassLoader->load($classFile);
            } catch (Throwable $exception) {
                $output->writeln('<fg=red>[!] exception inspecting ' . $classFile->getPathname() . ', skipping</>');
                continue;
            }

            $appliedRules = 0;
            foreach ($this->ruleRegistry->getAllRules() as $name => $rule) {
                try {
                    if (!$rule->supports($reflectionClass)) {
                        $output->writeln(
                            'rule ' . $name . ' does not support ' . $reflectionClass->getName(),
                            OutputInterface::VERBOSITY_DEBUG
                        );
                        continue;
                    }
                } catch (Throwable $throwable) {
                    $output->writeln('<fg=red>[!] exception inspecting ' . $classFile->getPathname() . ', skipping</>');
                    continue;
                }
                if (0 === $appliedRules++) {
                    $output->writeln('<options=bold>' . $reflectionClass->getName() . '</>');
                }
                try {
                    $result = $rule->execute($reflectionClass);
                    if ($result === true || $result === null) {
                        $results->incrementPasses();
                        $output->writeln("<fg=green>\t✓ " . $rule->getName() . '</>');
                    } else {
                        $results->incrementFailures();
                        $results->logFailure($reflectionClass->getName(), $rule);
                        $output->writeln("<fg=red>\tx " . $rule->getName() . ' (' . $rule->getDescription() . ')' . '</>');
                    }
                } catch (FailedAssertionException $assertionException) {
                    $results->incrementFailures();
                    $results->logFailure(
                        $reflectionClass->getName(),
                        $rule,
                        $assertionException->getMessage()
                    );
                    $output->writeln("<fg=red>\tx " . $rule->getName() . ' (' . $rule->getDescription() . ')' . '</>');
                } catch (Throwable $throwable) {
                    $results->incrementFailures();
                    $results->logException($reflectionClass->getName(), $rule, $throwable->getMessage());
                    $output->writeln("<fg=red;options=bold>\t! " . $rule->getName() . ' (' . $throwable->getMessage() . ')</>');
                }
            }

            if ($appliedRules === 0) {
                $output->writeln(
                    'no rules found for ' . $reflectionClass->getName(),
                    OutputInterface::VERBOSITY_DEBUG
                );
            }
        }

        if ($results->getFailures() > 0) {
            $symfonyStyle->error($results->getFailures() . ' failures!');
            $table = new Table($output);
            $table->setHeaders([
                '<fg=red>Class</>',
                '<fg=red>Rule</>',
                '<fg=red>Description</>',
                '<fg=red>Status</>',
                '<fg=red>Message</>',
            ]);
            $table->setRows($results->getFailureArray());
            $table->setColumnMaxWidth(2, 50);
            $table->setColumnMaxWidth(4, 50);
            $table->render();
            if ($input->getOption('dry-run')) {
                return 0;
            }

            return 1;
        }
        $symfonyStyle->success('all rules pass');

        return 0;
    }
}
