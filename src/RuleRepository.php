<?php declare(strict_types=1);


namespace WagLabs\PawfectPHP;


/**
 * Class RuleRepository
 * @package WagLabs\PawfectPHP
 * @author  Andrew Breksa <andrew.breksa@wagwalking.com>
 */
class RuleRepository implements RuleRepositoryInterface
{

    /**
     * @var array<string, RuleInterface>
     */
    protected $rules = [];

    /**
     * @param string $name
     * @return RuleInterface
     */
    public function getRule(string $name): RuleInterface
    {
        return $this->rules[$name];
    }

    /**
     * @return array<string, RuleInterface>
     */
    public function getAllRules(): array
    {
        return $this->rules;
    }

    /**
     * @param string        $name
     * @param RuleInterface $rule
     */
    public function register(string $name, RuleInterface $rule): void
    {
        $this->rules[$name] = $rule;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->rules);
    }
}
