<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\Examples\Source;

use Doctrine\Common\Annotations\Annotation\Required;
use WagLabs\PawfectPHP\Annotations\ApplyRule;

/**
 * Class AnnotatedClass
 * @ApplyRule
 * @ApplyRule("single-rule")
 * @ApplyRule({"rule-1", "rule-2"})
 * @ApplyRule(names={"rule-1", "rule-2"})
 * @ApplyRule(names="invalid")
 * @ApplyRule(regex="/^starts-with-/")
 * @ApplyRule(regex="invalid")
 * @ApplyRule(names={"rule-1", "rule-2"}, regex="/^won't-be-tested/")
 * @ApplyRule("override", names={"rule-1", "rule-2"}, regex="/^this-either/")
 */
class AnnotatedClass
{
    /**
     * @var mixed
     * @Required
     */
    protected $test;

    /**
     * @return mixed
     * @Required
     */
    public function getTest()
    {
        return $this->test;
    }
}
