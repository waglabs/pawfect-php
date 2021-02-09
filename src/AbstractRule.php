<?php declare(strict_types=1);

namespace WagLabs\PawfectPHP;

/**
 * Class AbstractRule
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
abstract class AbstractRule implements RuleInterface
{

    /**
     * @param bool   $condition
     * @param string $message
     * @return void
     * @throws FailedAssertionException
     */
    public function assert(bool $condition, string $message = 'Failed assertion'): void
    {
        if (!$condition) {
            throw new FailedAssertionException(
                $message
            );
        }
    }

}
