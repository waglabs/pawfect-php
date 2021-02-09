<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP;

/**
 * Interface RuleRepositoryInterface
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
interface RuleRepositoryInterface
{
    /**
     * @param string $name
     * @return RuleInterface
     */
    public function getRule(string $name): RuleInterface;

    /**
     * @return array<RuleInterface>
     */
    public function getAllRules(): array;

    /**
     * @param string        $name
     * @param RuleInterface $rule
     */
    public function register(string $name, RuleInterface $rule): void;

    /**
     * @return int
     */
    public function count(): int;

}
