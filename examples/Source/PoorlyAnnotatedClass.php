<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP\Examples\Source;

use WagLabs\PawfectPHP\Annotations\ApplyRule;

/**
 * Class AnnotatedClass
 * @ApplyRule
 * @asdf
 * @qwer
 * @zxcv
 * @wert
 * @sdfg
 * @xcvb
 * @erty
 * @dfgh
 * @cvbn
 * @poiu
 * @lkjh
 */
class PoorlyAnnotatedClass
{
    /**
     * @var mixed
     */
    protected $test;

    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }
}
