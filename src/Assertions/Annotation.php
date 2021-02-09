<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\Assertions;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionMethod;
use ReflectionProperty;
use WagLabs\PawfectPHP\Annotations\ApplyRule;
use WagLabs\PawfectPHP\ReflectionClass;

trait Annotation
{
    /** @var AnnotationReader|null */
    private $annotationReader;

    /**
     * @param ReflectionClass $reflectionClass
     * @param string|null     $annotationClass
     *
     * @return bool
     */
    public function hasAnnotation(ReflectionClass $reflectionClass, string $annotationClass = null): bool
    {
        return $this->hasClassAnnotation($reflectionClass, $annotationClass)
            || $this->hasPropertyAnnotation($reflectionClass, $annotationClass)
            || $this->hasMethodAnnotation($reflectionClass, $annotationClass);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string|null     $annotationClass
     *
     * @return bool
     */
    public function hasClassAnnotation(ReflectionClass $reflectionClass, string $annotationClass = null): bool
    {
        return !empty($this->getClassAnnotations($reflectionClass, $annotationClass));
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string|null     $annotationClass
     *
     * @return bool
     */
    public function hasPropertyAnnotation(ReflectionClass $reflectionClass, string $annotationClass = null): bool
    {
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            if (!empty($this->getPropertyAnnotations($reflectionClass, $property->getName(), $annotationClass))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string|null     $annotationClass
     *
     * @return bool
     */
    public function hasMethodAnnotation(ReflectionClass $reflectionClass, string $annotationClass = null): bool
    {
        $methods = $reflectionClass->getMethods();
        foreach ($methods as $method) {
            if (!empty($this->getMethodAnnotations($reflectionClass, $method->getName(), $annotationClass))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $ruleName
     *
     * @return bool
     */
    public function matchesApplyRuleAnnotation(ReflectionClass $reflectionClass, string $ruleName): bool
    {
        $annotations = $this->getClassAnnotations($reflectionClass, ApplyRule::class);
        /** @var ApplyRule $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation->matches($ruleName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string|null     $annotationName
     *
     * @return array<object>
     */
    protected function getClassAnnotations(ReflectionClass $reflectionClass, string $annotationName = null): array
    {
        $coreReflectionClass = new \ReflectionClass($reflectionClass->getName());
        $annotations = $this->protectFromUnknownAnnotations(function () use ($coreReflectionClass) {
            return $this->getAnnotationReader()->getClassAnnotations($coreReflectionClass);
        });

        return $this->filterAnnotations($annotations, $annotationName);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $propertyName
     * @param string|null     $annotationName
     *
     * @return array<object>
     */
    protected function getPropertyAnnotations(ReflectionClass $reflectionClass, string $propertyName, string $annotationName = null): array
    {
        $coreReflectionProperty = new ReflectionProperty($reflectionClass->getName(), $propertyName);
        $annotations = $this->protectFromUnknownAnnotations(function () use ($coreReflectionProperty) {
            return $this->getAnnotationReader()->getPropertyAnnotations($coreReflectionProperty);
        });

        return $this->filterAnnotations($annotations, $annotationName);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @param string          $methodName
     * @param string|null     $annotationName
     *
     * @return array<object>
     */
    protected function getMethodAnnotations(ReflectionClass $reflectionClass, string $methodName, string $annotationName = null): array
    {
        $coreReflectionMethod = new ReflectionMethod($reflectionClass->getName(), $methodName);
        $annotations = $this->protectFromUnknownAnnotations(function () use ($coreReflectionMethod) {
            return $this->getAnnotationReader()->getMethodAnnotations($coreReflectionMethod);
        });

        return $this->filterAnnotations($annotations, $annotationName);
    }

    /**
     * @psalm-param callable(): array<object> $get
     *
     * @return array<object>
     */
    private function protectFromUnknownAnnotations(callable $get): array
    {
        $i = 0;
        do {
            $i++;
            $tryAgain = false;
            try {
                return $get();
            }
            catch (AnnotationException $exception) {
                if (preg_match('/annotation "@([^"]+)"/', $exception->getMessage(), $matches)) {
                    $tryAgain = true;
                    AnnotationReader::addGlobalIgnoredName($matches[1]);
                }
            }
        } while ($tryAgain && $i < 10);

        return [];
    }

    /**
     * @param array<object> $annotations
     * @param string|null   $annotationName
     *
     * @return array<object>
     */
    private function filterAnnotations(array $annotations, string $annotationName = null): array
    {
        return is_null($annotationName)
            ? $annotations
            : array_filter($annotations, function ($annotation) use ($annotationName) {
                return $annotation instanceof $annotationName;
            });
    }

    /**
     * @return AnnotationReader
     */
    private function getAnnotationReader(): AnnotationReader
    {
        if (!isset($this->annotationReader)) {
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }
}
