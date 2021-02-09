<?php


namespace WagLabs\PawfectPHP\Examples\Rules;


use WagLabs\PawfectPHP\AbstractRule;
use WagLabs\PawfectPHP\FileLoader\FileLoaderInterface;
use WagLabs\PawfectPHP\ReflectionClass;
use WagLabs\PawfectPHP\ReflectionClassLoaderInterface;
use WagLabs\PawfectPHP\RuleRepository;
use WagLabs\PawfectPHP\RuleRepositoryInterface;

/**
 * Class ComplexRule
 * @package WagLabs\PawfectPHP\Examples\Rules
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ComplexRule extends AbstractRule
{

    /**
     * @var ReflectionClassLoaderInterface
     */
    protected $reflectionClassLoader;

    /**
     * ComplexRule constructor.
     * @param ReflectionClassLoaderInterface $reflectionClassLoader
     */
    public function __construct(ReflectionClassLoaderInterface $reflectionClassLoader)
    {
        $this->reflectionClassLoader = $reflectionClassLoader;
    }


    public function supports(ReflectionClass $reflectionClass): bool
    {
        return
            $reflectionClass->implementsInterface(FileLoaderInterface::class)
            ||
            $reflectionClass->implementsInterface(RuleRepositoryInterface::class);
    }


    public function execute(ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->implementsInterface(FileLoaderInterface::class)) {
            $this->assert($reflectionClass->hasMethod('yieldFiles'));
            $this->assert(!$reflectionClass->getMethod('yieldFiles')->hasReturnType());
            return;
        }

        if ($reflectionClass->getName() === RuleRepository::class) {
            $this->assert($reflectionClass->hasProperty('rules'));
            $this->assert($reflectionClass->getProperty('rules')->isProtected(), 'Rules property is not protected');
        }
    }

    public function getName(): string
    {
        return 'complex-rule';
    }

    public function getDescription(): string
    {
        return 'a complex rule to demonstrate advanced functionality';
    }
}
