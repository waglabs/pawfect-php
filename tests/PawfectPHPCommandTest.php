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
use Symfony\Component\Console\Tester\CommandTester;
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
        //  Mockery::close();
        parent::tearDown();
    }

    public function testNoRules()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(0)->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([])
            ->once();

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
                'paths' => __DIR__ . '/../src'
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testNoRulesWithRuleInterface()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(0)->once();
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(false)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule')
            ->once();
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

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
                'paths' => __DIR__ . '/../src'
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testExceptionLoadingRule()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(0)->once();
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andThrow(Exception::class)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

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
                'paths' => __DIR__ . '/../src'
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('no rules found', $output);
        self::assertStringContainsString('exception inspecting TestRule.php, skipping', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testNoClasses()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule')
            ->once();
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testNoRulesForClass()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule')
            ->once();
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnFalse()
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRulePassesTrueResponse()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnTrue()
            ->once();

        $testRule->shouldReceive('execute')->with($testClassReflectionClass)
            ->andReturn(true)
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRulePassesNullResponse()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnTrue()
            ->once();

        $testRule->shouldReceive('execute')->with($testClassReflectionClass)
            ->andReturn(null)
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRuleFailsFalseResponse()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnTrue()
            ->once();

        $testRule->shouldReceive('execute')->with($testClassReflectionClass)
            ->andReturn(false)
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('1 failures!', $output);
        self::assertStringContainsString('this is a description', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testRuleFailsAssertionException()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnTrue()
            ->once();

        $testRule->shouldReceive('execute')->with($testClassReflectionClass)
            ->andThrow(new FailedAssertionException())
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('1 failures!', $output);
        self::assertStringContainsString('this is a description', $output);
        self::assertStringNotContainsString('exception', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testRuleFailsException()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnTrue()
            ->once();

        $testRule->shouldReceive('execute')->with($testClassReflectionClass)
            ->andThrow(new Exception())
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('1 failures!', $output);
        self::assertStringContainsString('this is a description', $output);
        self::assertStringContainsString('exception', $output);
        self::assertEquals(1, $commandTester->getStatusCode());
    }

    public function testRuleSupportsException()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->andThrow(new Exception())
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('exception inspecting TestClass.php, skipping', $output);
        self::assertStringContainsString('[OK] all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testExceptionLoadingClass()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andThrow(Exception::class)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                'paths' => [__DIR__ . '/../src']
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('exception inspecting TestClass.php, skipping', $output);
        self::assertStringContainsString('[OK] all rules pass', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }

    public function testRuleFailsExceptionDryRun()
    {
        $fileLoader            = Mockery::mock(FileLoaderInterface::class);
        $ruleRegistry          = Mockery::mock(RuleRepositoryInterface::class);
        $reflectionClassLoader = Mockery::mock(ReflectionClassLoaderInterface::class);
        $container             = Mockery::mock(ContainerInterface::class);

        $ruleRegistry->shouldReceive('count')->andReturn(1)->once();
        $testRule = Mockery::mock(RuleInterface::class);
        $testRule->shouldReceive('getName')
            ->andReturn('test-rule');
        $testRule->shouldReceive('getDescription')
            ->andReturn('this is a description');
        $testRuleReflectionClass = Mockery::mock(ReflectionClass::class);
        $testRuleReflectionClass->shouldReceive('implementsInterface')
            ->with(RuleInterface::class)
            ->andReturn(true)
            ->once();
        $testRuleReflectionClass->shouldReceive('getName')
            ->andReturn('TestRule');
        $testRuleFile = Mockery::mock(SplFileInfo::class);
        $testRuleFile->shouldReceive('getPathname')->andReturn('TestRule.php');

        $testClassFile = Mockery::mock(SplFileInfo::class);
        $testClassFile->shouldReceive('getPathname')->andReturn('TestClass.php');

        $testClassReflectionClass = Mockery::mock(ReflectionClass::class);
        $testClassReflectionClass->shouldReceive('getSplFileInfo')
            ->andReturn($testClassFile);
        $testClassReflectionClass->shouldReceive('getName')
            ->andReturn('TestClass');
        $testRule->shouldReceive('supports')
            ->with($testClassReflectionClass)
            ->andReturnTrue()
            ->once();

        $testRule->shouldReceive('execute')->with($testClassReflectionClass)
            ->andThrow(new Exception())
            ->once();

        $ruleRegistry->shouldReceive('register')
            ->with('test-rule', $testRule)
            ->once();
        $ruleRegistry->shouldReceive('getAllRules')
            ->andReturn(['test-rule' => $testRule])
            ->once();

        $container->shouldReceive('get')
            ->with('TestRule')
            ->andReturn($testRule)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testRuleFile)
            ->andReturn($testRuleReflectionClass)
            ->once();

        $reflectionClassLoader->shouldReceive('load')
            ->with($testClassFile)
            ->andReturn($testClassReflectionClass)
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../examples/'])
            ->andReturn([
                $testRuleFile
            ])
            ->once();

        $fileLoader->shouldReceive('yieldFiles')
            ->with([__DIR__ . '/../src'])
            ->andReturn([
                $testClassFile
            ])
            ->once();

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
                '--dry-run' => true
            ]
        );

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('1 failures!', $output);
        self::assertStringContainsString('this is a description', $output);
        self::assertStringContainsString('exception', $output);
        self::assertEquals(0, $commandTester->getStatusCode());
    }
}
