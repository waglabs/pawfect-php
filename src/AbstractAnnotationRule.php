<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP;

use WagLabs\PawfectPHP\Assertions\Annotation;

abstract class AbstractAnnotationRule extends AbstractRule
{
    use Annotation;
    /**
     * @inheritDoc
     */
    public function supports(ReflectionClass $reflectionClass): bool
    {
        return $this->matchesApplyRuleAnnotation($reflectionClass, $this->getName());
    }
}
