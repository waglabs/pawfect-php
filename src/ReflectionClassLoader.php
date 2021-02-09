<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP;


use Doctrine\Common\Annotations\PhpParser;
use Exception;
use ReflectionException;
use Roave\BetterReflection\Reflection\Adapter\ReflectionClass as ReflectionClassAdapter;
use Roave\BetterReflection\Reflection\ReflectionClass as BetterReflectionClass;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use SplFileInfo;

/**
 * Class ReflectionClassLoader
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class ReflectionClassLoader implements ReflectionClassLoaderInterface
{

    /**
     * @var Locator
     */
    protected $astLocator;

    /**
     * @var array<string, ReflectionClass>
     */
    protected $fileClassCache = [];

    /**
     * ReflectionClassLoader constructor.
     * @param Locator $astLocator
     */
    public function __construct(Locator $astLocator)
    {
        $this->astLocator = $astLocator;
    }


    /**
     * @param SplFileInfo $splFileInfo
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function load(SplFileInfo $splFileInfo): ReflectionClass
    {
        if (array_key_exists(sha1($splFileInfo->getPathname()), $this->fileClassCache)) {
            return $this->fileClassCache[sha1($splFileInfo->getPathname())];
        }
        $reflector = new ClassReflector(new SingleFileSourceLocator($splFileInfo->getPathname(), $this->astLocator));
        $classes = $reflector->getAllClasses();
        if (count($classes) !== 1) {
            throw new Exception('unable to load a class in ' . $splFileInfo->getPathname());
        }

        $reflectionClass = $this->loadFromFqn($classes[0]->getName());
        $this->fileClassCache[sha1($splFileInfo->getPathname())] = $reflectionClass;
        return $reflectionClass;
    }

    /**
     * @param string           $fqn
     * @param SplFileInfo|null $splFileInfo
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function loadFromFqn(string $fqn, ?SplFileInfo $splFileInfo = null): ReflectionClass
    {
        $betterReflectionClass = BetterReflectionClass::createFromName($fqn);

        $usesNames = array_values(
            (new PhpParser())->parseClass(new ReflectionClassAdapter($betterReflectionClass))
        );
        $reflectionClass = new ReflectionClass(
            $splFileInfo,
            $betterReflectionClass,
            $usesNames
        );

        return $reflectionClass;
    }
}
