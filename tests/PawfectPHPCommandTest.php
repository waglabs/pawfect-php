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

namespace WagLabs\PawfectPHP\Tests;

use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use SplFileInfo;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;
use WagLabs\PawfectPHP\Analysis;
use WagLabs\PawfectPHP\AnalysisAwareRule;
use WagLabs\PawfectPHP\Exceptions\NoSupportedClassesFoundInFile;
use WagLabs\PawfectPHP\FailedAssertionException;
use WagLabs\PawfectPHP\FileLoader\FileLoaderInterface;
use WagLabs\PawfectPHP\PawfectPHPCommand;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\ReflectionClassLoaderInterface;
use WagLabs\PawfectPHP\RuleInterface;
use WagLabs\PawfectPHP\RuleRepositoryInterface;

/**
 * Class PawfectPHPCommandTest
 * @package WagLabs\PawfectPHP\Tests
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class PawfectPHPCommandTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testNoRules(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(0);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => __DIR__ . '/../src',
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testNoRulesWithRuleInterface(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(0);
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(false);
        $testRuleReflectionClass->expects('implementsInterface')->with(AnalysisAwareRule::class)->andReturns(false);
        $testRuleReflectionClass->expects('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => __DIR__ . '/../src',
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testExceptionLoadingRule(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(0);
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andThrow(Exception::class);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                        'rules' => __DIR__ . '/../examples/',
                        'paths' => __DIR__ . '/../src',
                ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertStringContainsString('exception inspecting TestRule.php, skipping', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testNoSupportedClassesFoundInFileWhileLoadingRuleFile(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(0);
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andThrow(NoSupportedClassesFoundInFile::class);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
                $testRuleFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                        'rules' => __DIR__ . '/../examples/',
                        'paths' => __DIR__ . '/../src',
                ],
            [
                        'verbosity' => ConsoleOutput::VERBOSITY_DEBUG,
                ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertStringContainsString('no supported classes found in TestRule.php', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testNoClasses(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $ruleRegistry->expects('getAllRules')->andReturn([
                'test-rule' => $testRule,
        ]);
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $ruleRegistry->expects('register')->with('test-rule', $testRule);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                        'rules' => __DIR__ . '/../examples/',
                        'paths' => [__DIR__ . '/../src'],
                ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRuleSkipped(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('TestRule');
        $testRuleNotSkipped = Mockery::mock(AnalysisAwareRule::class);
        $testRuleNotSkipped->allows('getName')->andReturns('TestRuleNotSkipped');
        $ruleRegistry->expects('getAllRules')->andReturn([
                'TestRuleNotSkipped' => $testRuleNotSkipped,
        ]);
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testRuleNoTskippedReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleNoTskippedReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(false);
        $testRuleNoTskippedReflectionClass->expects('implementsInterface')->with(AnalysisAwareRule::class)->andReturns(true);
        $testRuleNoTskippedReflectionClass->allows('getName')->andReturns('TestRuleNotSkipped');
        $testRuleNotSkippedFile = Mockery::mock(SplFileInfo::class);
        $testRuleNotSkippedFile->allows('getPathname')->andReturns('TestRuleNotSkipped.php');

        $container->expects('get')->with('TestRule')->andReturns($testRule);
        $container->expects('get')->with('TestRuleNotSkipped')->andReturns($testRuleNotSkipped);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);
        $reflectionClassLoader->expects('load')->with($testRuleNotSkippedFile)->andReturns($testRuleNoTskippedReflectionClass);

        $ruleRegistry->expects('register')->with('TestRuleNotSkipped', $testRuleNotSkipped);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
                $testRuleFile,
                $testRuleNotSkippedFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                        'rules'  => __DIR__ . '/../examples/',
                        'paths'  => [__DIR__ . '/../src'],
                        '--skip' => ['TestRule'],
                ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testNoRulesForClass(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnFalse();

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRulePassesTrueResponse(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')->with($testClassReflectionClass)->andReturns(true);

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRulePassesNullResponse(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')->with($testClassReflectionClass)->andReturns(null);

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRuleFailsFalseResponse(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')->with($testClassReflectionClass)->andReturns(false);

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('1 failure', $output);
        self::assertStringContainsString('this is a description', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testRuleFailsAssertionException(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')->with($testClassReflectionClass)->andThrow(new FailedAssertionException());

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Failures: 1', $output);
        self::assertStringContainsString('WagLabs_PawfectPHP_RuleInterface', $output);
        self::assertStringContainsString('Exceptions: 0', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testRuleFailsException(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')->with($testClassReflectionClass)->andThrow(new Exception());

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Exceptions: 1', $output);
        self::assertStringContainsString('exception running rule Mockery', $output);
        self::assertStringContainsString('WagLabs_PawfectPHP_RuleInterface', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testRuleSupportsException(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->andThrow(new Exception());

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('exception checking if TestClass.php is supported by Mockery', $output);
        self::assertStringContainsString('WagLabs_PawfectPHP_RuleInterface, skipping', $output);
        self::assertStringContainsString('[OK] all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExceptionLoadingClass(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $ruleRegistry->expects('getAllRules')->andReturns([
            'test-rule' => $testRule,
        ]);
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $ruleRegistry->expects('register')->with('test-rule', $testRule);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andThrow(Exception::class);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                    'rules' => __DIR__ . '/../examples/',
                    'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('exception inspecting TestClass.php, skipping', $output);
        self::assertStringContainsString('[OK] all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testNoSupportedClassesFoundInFileWhileLoadingClassFile(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $ruleRegistry->expects('getAllRules')->andReturns([
                'test-rule' => $testRule,
        ]);
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $ruleRegistry->expects('register')->with('test-rule', $testRule);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andThrow(NoSupportedClassesFoundInFile::class);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
                $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
                $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                        'rules' => __DIR__ . '/../examples/',
                        'paths' => [__DIR__ . '/../src'],
                ],
            [
                        'verbosity' => ConsoleOutput::VERBOSITY_DEBUG,
                ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('[OK] all rules pass', $output);
        self::assertStringContainsString('no supported classes found in TestClass.php', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRuleFailsExceptionDryRun(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')->with($testClassReflectionClass)->andThrow(new Exception('test exception'));

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules'     => __DIR__ . '/../examples/',
                'paths'     => [__DIR__ . '/../src'],
                '--dry-run' => true,
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Exceptions: 1', $output);
        self::assertStringContainsString('test exception', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testAnalysisAwareRulePassesNullResponse(): void
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->expects('count')->andReturns(1);
        $testRule = Mockery::mock(AnalysisAwareRule::class);
        $testRule->allows('getName')->andReturns('test-rule');
        $testRule->allows('getDescription')->andReturns('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->expects('implementsInterface')->with(RuleInterface::class)->andReturns(false);
        $testRuleReflectionClass->expects('implementsInterface')->with(AnalysisAwareRule::class)->andReturns(true);
        $testRuleReflectionClass->allows('getName')->andReturns('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->allows('getPathname')->andReturns('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->allows('getPathname')->andReturns('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->allows('getSplFileInfo')->andReturns($testClassFile);
        $testClassReflectionClass->allows('getName')->andReturns('TestClass');
        $testRule->expects('supports')->with($testClassReflectionClass)->andReturnTrue();

        $testRule->expects('execute')
            ->withArgs(function ($reflectionClass, Analysis $analysis) use ($testRule) {
                $analysis->warn($reflectionClass, $testRule, 'test', 1);
                return true;
            })->andReturns(null);

        $ruleRegistry->expects('register')->with('test-rule', $testRule);
        $ruleRegistry->expects('getAllRules')->andReturns(['test-rule' => $testRule]);

        $container->expects('get')->with('TestRule')->andReturns($testRule);

        $reflectionClassLoader->expects('load')->with($testRuleFile)->andReturns($testRuleReflectionClass);

        $reflectionClassLoader->expects('load')->with($testClassFile)->andReturns($testClassReflectionClass);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../examples/'])->andReturns([
            $testRuleFile,
        ]);

        $fileLoader->expects('yieldFiles')->with([__DIR__ . '/../src'])->andReturns([
            $testClassFile,
        ]);

        $command = new PawfectPHPCommand(
            $fileLoader,
            $ruleRegistry,
            $reflectionClassLoader,
            $container
        );


        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'rules' => __DIR__ . '/../examples/',
                'paths' => [__DIR__ . '/../src'],
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
